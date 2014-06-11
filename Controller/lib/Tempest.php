<?php

/**
 * Tempest for PHP
 * 
 * Tempest is implemented natively in multiple programming languages, including
 * PHP 5.  This implementation is "pure" PHP, meaning that there is no
 * extension to configure or compile.  Installation can be as simple as using
 * the following PEAR commands:
 * 
 * <code>
 * pear channel-discover pear.digitalflophouse.com
 * pear install digitalflophouse/Tempest
 * </code>
 * 
 * @author Evan Kaufman
 * @version 2010.09.26
 * @version API 2009.07.15
 * @package Tempest
 * @license http://www.opensource.org/licenses/mit-license.php
 */

/**
 * Tempest class
 * 
 * This class exposes the Tempest API through class instantiation:
 * 
 * <code>
 * <?php
 * include_once('Tempest.php');
 * 
 * // Create new instance
 * $heatmap = new Tempest(array(
 *   'input_file' => 'screenshot.png',
 *   'output_file' => 'heatmap.png',
 *   'coordinates' => array( array(0,10), array(2,14), array(2,14) ),
 * ));
 * 
 * // Configure as needed
 * $heatmap->set_image_lib( Tempest::LIB_MAGICK );
 * 
 * // Generate and write heatmap image
 * $heatmap->render();
 * ?>
 * </code>
 * 
 * @package Tempest
 */
class Tempest {
    /**
     * Implementation version
     * @ignore
     */
    const VERSION = '2010.09.26';
    
    /**
     * API version
     * @ignore
     */
    const API_VERSION = '2009.07.15';
    
    /**
     * For forcing use of {@link Imagick.php Imagick} support.
     * @see $image_lib
     */
    const LIB_IMAGICK = 'imagick';
    
    /**
     * For forcing use of {@link Magickwand.php Magickwand} support.
     * @see $image_lib
     */
    const LIB_MAGICKWAND = 'magickwand';
    
    /**
     * For forcing use of {@link Gd.php Gd} support.
     * @see $image_lib
     */
    const LIB_GD = 'gd';
    
    /**
     * Path to an existing image file
     * 
     * The generated heatmap will share the same dimensions as this image,
     * and - if indicated - will be overlaid onto this image with a given
     * opacity.
     */
    protected $input_file;
    
    /**
     * Path to an image file
     * 
     * The generated heatmap will be written to this path, replacing any
     * existing file without warning.
     */
    protected $output_file;
    
    /**
     * Array containing multiple arrays of x,y coordinates
     * 
     * The contained x,y coordinates will mark the center of all plotted data
     * points in the heatmap.  Coordinates can - and in many cases are
     * expected to - be repeated.
     */
    protected $coordinates;
    
    /**
     * Path to an existing image file
     * 
     * This image, expected to be greyscale, is used to plot data points for
     * each of the given coordinates.  Defaults to a bundled image, if none
     * is provided.
     */
    protected $plot_file;
    
    /**
     * Path to an existing image file
     * 
     * This image, expected to be a true color vertical gradient, is used as a
     * color lookup table and is applied to the generated heatmap.  Defaults
     * to a bundled image, if none is provided.
     */
    protected $color_file;
    
    /**
     * Boolean indicator
     * 
     * If true, the heatmap is overlaid onto the input image with a given
     * opacity before being written to the filesystem.  Defaults to <b>True</b>.
     */
    protected $overlay = TRUE;
    
    /**
     * Percentage from 0 to 100
     * 
     * Indicates with what percentage of opaqueness to overlay the heatmap
     * onto the input image.  If 0, the heatmap will not be visible; if 100,
     * the input image will not be visible.  Defaults to <b>50</b>
     */
    protected $opacity = 50;
    
    /**
     * Constant for a supported image library
     * 
     * Indicates which supported image manipulation library should be used
     * for rendering operations.  Defaults to the first available from the
     * following:
     * 
     * <ul>
     *   <li>{@link LIB_IMAGICK Imagick}</li>
     *   <li>{@link LIB_MAGICKWAND MagickWand}</li>
     *   <li>{@link LIB_GD GD}</li>
     * </ul>
     */
    protected $image_lib;
    
    /**
     * Class constructor, accepts an array of named arguments corresponding to
     * the class' own getter and setter methods.  The following named arguments
     * are required, all others are optional:
     *
     * <ul>
     *   <li>{@link set_input_file input_file}</li>
     *   <li>{@link set_output_file output_file}</li>
     *   <li>{@link set_coordinates coordinates}</li>
     * </ul>
     * 
     * @param   array    $params  array of named parameters
     * @return  Tempest           new class instance
     */
    public function __construct(array $params) {
        // set any defaults that require method calls
        $this->plot_file = dirname(__FILE__) . '/Tempest/plot.png';
        $this->color_file = dirname(__FILE__) . '/Tempest/clut.png';
        $this->image_lib = $this->_calc_image_lib();
        
        // for all required parameters...
        foreach (array('input_file', 'output_file', 'coordinates') as $param_name) {
            // ...ensure they were provided
            if(empty($params[$param_name])) {
                throw new Exception("Missing required parameter '{$param_name}'");
            }
            
            // ...and call each of their setters
            call_user_func( array($this, 'set_'.$param_name), $params[$param_name] );
        }
        
        // for all optional parameters...
        foreach(array('plot_file', 'color_file', 'overlay', 'opacity', 'image_lib') as $param_name) {
            // ...if they were provided...
            if(isset($params[$param_name])) {
                // ...call their setters
                call_user_func( array($this, 'set_'.$param_name), $params[$param_name] );
            }
        }
    }
    
    /**
     * Initiates processing of provided arguments, and writes a heatmap image
     * to the filesystem.  Returns <b>True</b> on success.
     * 
     * @return  boolean  status
     */
    public function render() {
        $lib_name = ucfirst($this->image_lib);
        require_once( dirname(__FILE__) . '/Tempest/' . $lib_name . '.php' );
        return eval("return Tempest_{$lib_name}::render(\$this);");
    }
    
    /**
     * Returns the version number of the current release of this implementation.
     * 
     * @return  string  version
     */
    public static function version() {
        return self::VERSION;
    }
    
    /**
     * Returns the version number of the currently supported Tempest API.
     * 
     * @return  string  version
     */
    public static function api_version() {
        return self::API_VERSION;
    }
    
    /**
     * Indicates whether the given image library is currently available for use.
     * 
     * @param  mixed  $image_lib  constant for a supported image library
     * @return  boolean  status
     **/
    public static function has_image_lib($image_lib) {
        if($image_lib == self::LIB_IMAGICK || $image_lib == self::LIB_MAGICKWAND || $image_lib == self::LIB_GD) {
            if(extension_loaded($image_lib)) {
                return TRUE;
            }
            else {
                return FALSE;
            }
        }
        else {
            throw new Exception("Image library '{$image_lib}' is not supported");
        }
    }
    
    /**
     * @param  string  $input_file  path to an existing image file
     * @return  Tempest  class instance
     * @see $input_file
     **/
    public function set_input_file($input_file) {
        if(is_readable($input_file)) {
            $this->input_file = $input_file;
        }
        else {
            throw new Exception("Image '{$input_file}' is not readable");
        }
        return $this;
    }
    
    /**
     * @return  string  path to an existing image file
     * @see $input_file
     */
    public function get_input_file() {
        return $this->input_file;
    }
    
    
    
    /**
     * @param  string  $output_file  path to an image file
     * @return  Tempest  class instance
     * @see $output_file
     **/
    public function set_output_file($output_file) {
        if((!file_exists($output_file)) || is_writable($output_file)) {
            $this->output_file = $output_file;
        }
        else {
            throw new Exception("Image '{$output_file}' is not writable");
        }
        return $this;
    }
    
    /**
     * @return  string  path to an image file
     * @see $output_file
     **/
    public function get_output_file() {
        return $this->output_file;
    }
    
    
    /**
     * @param  array  $coordinates  array containing multiple arrays of x,y coordinates
     * @return  Tempest  class instance
     * @see $coordinates
     **/
    public function set_coordinates(array $coordinates) {
        $normal = array();
        
        // verify an array of 2-element arrays
        foreach($coordinates as $pair) {
            if(!is_array($pair) || count($pair) != 2) {
                throw new Exception('Bad coordinate pair: ' . print_r($pair, TRUE));
            }
        }
        
        $this->coordinates = $coordinates;
        return $this;
    }
    
    /**
     * @return  array  array containing multiple arrays of x,y coordinates
     * @see $coordinates
     **/
    public function get_coordinates() {
        return $this->coordinates;
    }
    
    
    /**
     * @param  string  $plot_file  path to an existing image file
     * @return  Tempest  class instance
     * @see $plot_file
     **/
    public function set_plot_file($plot_file) {
        if(is_readable($plot_file)) {
            $this->plot_file = $plot_file;
        }
        else {
            throw new Exception("Image '{$plot_file}' is not readable");
        }
        return $this;
    }
    
    /**
     * @return  string  path to an existing image file
     * @see $plot_file
     **/
    public function get_plot_file() {
        return $this->plot_file;
    }
    
    
    /**
     * @param  string  $color_file  path to an existing image file
     * @return  Tempest  class instance
     * @see $color_file
     **/
    public function set_color_file($color_file) {
        if(is_readable($color_file)) {
            $this->color_file = $color_file;
        }
        else {
            throw new Exception("Image '{$color_file}' is not readable");
        }
        return $this;
    }
    
    /**
     * @return  string  path to an existing image file
     * @see $color_file
     **/
    public function get_color_file() {
        return $this->color_file;
    }
    
    
    /**
     * @param  boolean  $overlay  boolean indicator
     * @return  Tempest  class instance
     * @see $overlay
     **/
    public function set_overlay($overlay) {
        $this->overlay = (bool) $overlay;
        return $this;
    }
    
    /**
     * @return  boolean  boolean indicator
     * @see $overlay
     **/
    public function get_overlay() {
        return $this->overlay;
    }
    
    /**
     * @param  integer  $opacity  percentage from 0 to 100
     * @return  Tempest  class instance
     * @see $opacity
     **/
    public function set_opacity($opacity) {
        if($opacity < 0 || $opacity > 100) {
            throw new Exception("'{$opacity}' is not a valid percentage (integer from 0 to 100)");
        }
        
        $this->opacity = (int) $opacity;
        return $this;
    }
    
    /**
     * @return  integer  percentage from 0 to 100
     * @see $opacity
     */
    public function get_opacity() {
        return $this->opacity;
    }
    
    /**
     * @param  mixed  $image_lib  constant for a supported image library
     * @return  Tempest  class instance
     * @see $image_lib
     **/
    public function set_image_lib($image_lib) {
        if($this->has_image_lib($image_lib)) {
            $this->image_lib = $image_lib;
        }
        else {
            throw new Exception("Image library '{$image_lib}' could not be found");
        }
        
        return $this;
    }
    
    /**
     * @return  mixed  constant for a supported image library
     * @see $image_lib
     **/
    public function get_image_lib() {
        return $this->image_lib;
    }
    
    /**
     * Determine optimal supported (and available) image library to use
     * @ignore
     **/
    protected function _calc_image_lib() {
        foreach (array(self::LIB_IMAGICK, self::LIB_MAGICKWAND, self::LIB_GD) as $image_lib) {
            if($this->has_image_lib($image_lib)) {
                return $image_lib;
            }
        }
        
        throw new Exception('No supported image library could be found');
    }
}
