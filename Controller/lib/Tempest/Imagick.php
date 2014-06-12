<?php

/**
 * Imagick adapter
 * 
 * This class implements Tempest image operations using the Imagick extension,
 * which in turn uses the ImageMagick API.
 * 
 * @author Evan Kaufman
 * @package Imagick
 * @link http://pecl.php.net/package/imagick
 * @link http://imagemagick.com/
 */
 
/**
 * Imagick adapter class
 * 
 * @package Imagick
 * @access private
 */

class Tempest_Imagick {
    public static $clut;
    
    public static function render($parent) {
        if(!isset(self::$clut)) {
            self::$clut = method_exists('Imagick', 'clutImage');
        }
        
        # load input file
        $input_file = new Imagick();
        $input_file->readImage($parent->get_input_file());
        
        # create new canvas file
        $input_size = $input_file->getImageGeometry();
        $output_file = new Imagick();
        # note: for imagick < 2.1.0, explicitly use pixel object to specify background color
        $output_file->newImage($input_size['width'], $input_size['height'], new ImagickPixel('none'));
        $output_file->setImageFormat('png');
        
        # do any necessary preprocessing & transformation of the coordinates
        $coordinates = $parent->get_coordinates();
        $max_rep = 0;
        $normal = array();
        foreach($coordinates as $pair) {
            # normalize repeated coordinate pairs
            $pair_key = "{$pair[0]}x{$pair[1]}";
            if(isset($normal[$pair_key])) {
                $normal[$pair_key][2]++;
            }
            else {
                $normal[$pair_key] = array($pair[0], $pair[1], 1);
            }
            # get the max repitition count of any single coord set in the data
            if($normal[$pair_key][2] > $max_rep) {
                $max_rep = $normal[$pair_key][2];
            }
        }
        $coordinates = array_values($normal);
        unset($normal);
        
        foreach ($coordinates as $pair) {
            # get the max repitition count of any single coord set in the data
            if($pair[2] > $max_rep) {
                $max_rep = $pair[2];
            }
        }
        
        # load plot image (presumably greyscale)
        $plot_file = new Imagick();
        $plot_file->readImage($parent->get_plot_file());
        
        # calculate coord correction based on plot image size
        $plot_size = $plot_file->getImageGeometry();
        $plot_correct = array( ($plot_size['width'] / 2), ($plot_size['height'] / 2) );
        
        # colorize opacity for how many times at most a point will be repeated
        # note: colorizeImage() in imagick < 2.3.0-rc2 has no effect and in imagick 2.3.0-rc2 doesn't respect opacity
        $colorize_percent = 99 / $max_rep;
        if($colorize_percent < 1) { $colorize_percent = 1; }
        self::colorize($plot_file, $colorize_percent);
        
        # paste one plot for each coordinate pair
        foreach($coordinates as $pair) {
            $x = ($pair[0] - $plot_correct[0]);
            $y = ($pair[1] - $plot_correct[1]);
            
            # for how many times coord pair was repeated
            for($i = 0; $i < $pair[2]; $i++) {
                # paste plot, centered on given coords
                $output_file->compositeImage(
                    $plot_file,
                    imagick::COMPOSITE_MULTIPLY,
                    $x,
                    $y
                );
            }
        }
        
        # destroy plot file, as we don't need it anymore
        $plot_file->destroy();
        
        # open color lookup table
        $color_file = new Imagick();
        $color_file->readImage($parent->get_color_file());
        
        # apply color lookup table with clut method if available
        if(self::$clut) {
            $output_file->clutImage($color_file);
        }
        # for older IM versions (anything before 6.3.5-7), use fx operator
        else {
            $color_file->addImage($output_file);
            $output_file = $color_file->fxImage("u.p{0,v*u.h}");
        }
        
        # destroy color file, as we don't need it anymore
        $color_file->destroy();
        
        # overlay heatmap over source image
        if($parent->get_overlay()) {
            $output_file->setImageOpacity(($parent->get_opacity() / 100));
            $input_file->compositeImage(
                $output_file,
                imagick::COMPOSITE_COPY,
                0,
                0
            );
            $output_file->destroy();
            $output_file = $input_file;
        }
        
        # write destination image
	    $output_file->transparentpaintimage(new ImagickPixel('black'), 0, 0, false);
        $output_file->writeImage($parent->get_output_file());
        
        # return true if successful
        return True;
    }
    
    protected static function colorize($image_file, $opacity) {
        # reduce percentage to fraction
        $opacity = (100 - $opacity) / 100;
        
        # iterate through each pixel in given image
        $iterator = $image_file->getPixelIterator();
        foreach($iterator as $row) {
            foreach ( $row as $pixel) {
                # work on rgb values
                $colors = $pixel->getColor();
                foreach(array('r', 'g', 'b') as $channel) {
                    $color = $colors[$channel];
                    if($color !== 255) {
                        $colors[$channel] = $color + ((255 - $color) * $opacity);
                    }
                }
                # build rgb triplet string
                $pixel->setColor("rgb({$colors['r']},{$colors['g']},{$colors['b']})");
            }
            
            # sync iterator after each iteration, to apply changes to original image
            $iterator->syncIterator();
        }
    }
}
