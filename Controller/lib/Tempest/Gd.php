<?php
/**
 * GD adapter
 * 
 * This class implements Tempest image operations using the GD library.  It is
 * generally recommended to use the version of the GD library that comes
 * bundled with PHP, but Tempest should be compatible either way.
 * 
 * @author Evan Kaufman
 * @package GD
 * @link http://www.libgd.org
 */
 
/**
 * GD adapter class
 * 
 * @package GD
 * @access private
 */
class Tempest_Gd {
    public static $colorize;
    
    public static function render($parent) {
        # determine whether built-in colorization is available
        if(!isset(self::$colorize)) {
            self::$colorize = function_exists('imagefilter') && defined('IMG_FILTER_COLORIZE');
        }
        
        # load source image (in order to get dimensions)
        $input_file = self::read($parent->get_input_file());

        # create object for destination image
        $output_file = imagecreatetruecolor( imagesx($input_file), imagesy($input_file) );
        $white_color = imagecolorallocate($output_file, 255, 255, 255);
        imagefill($output_file, 0, 0, $white_color);
        
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
        
        # load plot image (presumably greyscale)
        $plot_file = self::read($parent->get_plot_file());
        
        # calculate coord correction based on plot image size
        $plot_correct = array( (imagesx($plot_file) / 2), (imagesy($plot_file) / 2) );
        
        # colorize opacity for how many times at most a point will be repeated
        $colorize_percent = (99 / $max_rep);
        if($colorize_percent < 1) { $colorize_percent = 1; }
        
        # use built-in colorization if available
        if(self::$colorize) {
            imagefilter($plot_file, IMG_FILTER_COLORIZE, 255, 255, 255, ((128 * $colorize_percent) / 100));
        }
        else {
            self::colorize($plot_file, $colorize_percent);
        }
        
        # paste one plot for each coordinate pair
        foreach($coordinates as $pair) {
            $x = ($pair[0] - $plot_correct[0]);
            $y = ($pair[1] - $plot_correct[1]);
            
            # for how many times coord pair was repeated
            for($i = 0; $i < $pair[2]; $i++) {
                # paste plot, centered on given coords
                self::composite($output_file, $plot_file, $x, $y);
            }
        }
        
        # destroy plot file, as we don't need it anymore
        imagedestroy($plot_file);
        
        # open color lookup table
        $color_file = self::read($parent->get_color_file());
        
        # apply color lookup table
        $output_width = imagesx($output_file);
        $output_height = imagesy($output_file);
        $color_width = imagesx($color_file);
        $color_height = imagesy($color_file);
        
        $cached_colors = array();
        
        for($x = 0; $x < $output_width; $x++) {
            for($y = 0; $y < $output_height; $y++) {
                # calculate color lookup location
                $pixel_red = (imagecolorat($output_file, $x, $y) >> 16) & 0xFF;
                
                # cache colors as we look them up
                if(isset($cached_colors[$pixel_red])) {
                    $lookup_color = $cached_colors[$pixel_red];
                }
                else {
                    $color_offset = ($pixel_red / 255) * ($color_height - 1);
                    $lookup_color = self::getcolor( imagecolorat($color_file, 0, $color_offset) );
                    $cached_colors[$pixel_red] = $lookup_color;
                }
                
                # allocate and set new color from lookup table
                $new_color = imagecolorallocate($output_file, $lookup_color[0], $lookup_color[1], $lookup_color[2]);
                imagesetpixel($output_file, $x, $y, $new_color);
                imagecolordeallocate($output_file, $new_color);
            }
        }
        
        # overlay heatmap over source image
        if($parent->get_overlay() == True) {
            imagecopymerge($input_file, $output_file, 0, 0, 0, 0, $output_width, $output_height, $parent->get_opacity());
            imagedestroy($output_file);
            $output_file = $input_file;
        }
        
        # write destination image
        self::write($output_file, $parent->get_output_file());
        
        # return true if successful
        return True;
    }
    
    protected static function composite($source_image, $composite_image, $x, $y) {
        $cached_colors = array();
        
        # for each pixel from x to x+composite_width
        $composite_x = imagesx($composite_image) - 1;
        $composite_y = imagesy($composite_image) - 1;
        foreach(range(0, $composite_x) as $x_offset) {
            foreach(range(0, $composite_y) as $y_offset) {
                $source_x = ($x + $x_offset);
                $source_y = ($y + $y_offset);
                
                # skip negative coordinates
                if($source_x < 0 || $source_y < 0) { continue; }
                
                # get colors to composite together
                $source_color = imagecolorat($source_image, $source_x, $source_y);
                $composite_color = imagecolorat($composite_image, $x_offset, $y_offset);
                
                # cache colors as we multiply them
                if(isset($cached_colors[$source_color . 'x' . $composite_color])) {
                    $multiplied = $cached_colors[$source_color . 'x' . $composite_color];
                }
                else {
                    $multiplied = self::multiply($source_color, $composite_color);
                    $cached_colors[$source_color . 'x' . $composite_color] = $multiplied;
                }
                
                # allocate and set new colors after multiplication
                $multiplied_color = imagecolorallocate($source_image, $multiplied[0], $multiplied[1], $multiplied[2]);
                imagesetpixel($source_image, $source_x, $source_y, $multiplied_color);
                imagecolordeallocate($source_image, $multiplied_color);
            }
        }
    }
    
    protected static function multiply($color1, $color2) {
        $color1 = self::getcolor($color1);
        $color2 = self::getcolor($color2);
        
        return array(
            (($color1[0] / 255) * ($color2[0] / 255)) * 255,
            (($color1[1] / 255) * ($color2[1] / 255)) * 255,
            (($color1[2] / 255) * ($color2[2] / 255)) * 255,
        );
    }
    
    protected static function colorize($image_file, $opacity) {
        $cached_colors = array();
        
        # reduce percentage to fraction
        $opacity = (100 - $opacity) / 100;
        
        # get dimensions once
        $image_width = imagesx($image_file) - 1;
        $image_height = imagesy($image_file) - 1;
        
        # iterate through each pixel in given image
        foreach(range(0, $image_width) as $x) {
            foreach(range(0, $image_height) as $y) {
                # get color to colorize
                $color = imagecolorat($image_file, $x, $y);
                
                # cache colors as we colorize them
                if(isset($cached_colors[$color])) {
                    $rgb = $cached_colors[$color];
                }
                else {
                    $rgb = self::getcolor($color);
                    foreach($rgb as $index => $value) {
                        $rgb[$index] = $value + ((255 - $value) * $opacity);
                    }
                    $cached_colors[$color] = $rgb;
                }
                
                # allocate and set new colors after colorization
                $colorized_color = imagecolorallocate($image_file, $rgb[0], $rgb[1], $rgb[2]);
                imagesetpixel($image_file, $x, $y, $colorized_color);
                imagecolordeallocate($image_file, $colorized_color);
            }
        }
    }
    
    protected static function getcolor($color) {
        return array(
            (($color >> 16) & 0xFF),
            (($color >> 8) & 0xFF),
            ($color & 0xFF),
        );
    }
    
    protected static function read($filename) {
        # let GD decide what the image type is
        $image = imagecreatefromstring(file_get_contents($filename));
        # if not a truecolor image, convert it to one
        if(! imageistruecolor($image)) {
            $truecolor = imagecreatetruecolor(imagesx($image), imagesy($image));
            imagecopy($truecolor, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);
            $image = $truecolor;
        }
        
        return $image;
    }
    
    protected static function write($image, $filename) {
        # if potentially supported file type
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        if(preg_match('/^(gd2?|png|gif|w?bmp|jpe?g?)$/', $filetype)) {
            # adjust for file extension variations
            switch($filetype) {
                case 'jpg': case 'jpe':
                    $filetype = 'jpeg';
                    break;
                case 'bmp':
                    $filetype = 'wbmp';
                    break;
            }
            # if support exists, write file
            if(function_exists("image{$filetype}")) {
                call_user_func("image{$filetype}", $image, $filename);
                return TRUE;
            }
        }
        
        throw new Exception("No support for '{$filetype}' file format");
    }
}
