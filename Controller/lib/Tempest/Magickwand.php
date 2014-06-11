<?php

/**
 * Magickwand adapter
 * 
 * This class implements Tempest image operations using the MagickWand extension,
 * which in turn uses the ImageMagick MagickWand API.
 * 
 * @author Evan Kaufman
 * @package Magickwand
 * @link http://magickwand.org/
 * @link http://imagemagick.com/
 */
 
/**
 * Magickwand adapter class
 * 
 * @package Magickwand
 * @access private
 */

class Tempest_Magickwand {
    public static function render($parent) {
        # load source image (in order to get dimensions)
        $input_file = NewMagickWand();
        # @TODO: many of these methods return bools indicating success/failure...CHECK THEM
        MagickReadImage($input_file, $parent->get_input_file());
        
        # create object for destination image
        $output_file = NewMagickWand();
        MagickNewImage($output_file, MagickGetImageWidth($input_file), MagickGetImageHeight($input_file), 'white');
        MagickSetFormat($output_file, 'PNG');
        
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
        $plot_file = NewMagickWand();
        MagickReadImage($plot_file, $parent->get_plot_file());
        
        # calculate coord correction based on plot image size
        $plot_correct = array( (MagickGetImageWidth($plot_file) / 2), (MagickGetImageHeight($plot_file) / 2) );
        
        # colorize opacity for how many times at most a point will be repeated
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
                MagickCompositeImage(
                    $output_file,
                    $plot_file,
                    MW_MultiplyCompositeOp,
                    $x,
                    $y
                );
            }
        }
        
        # destroy plot file, as we don't need it anymore
        DestroyMagickWand($plot_file);
        
        # open color lookup table
        $color_file = NewMagickWand();
        MagickReadImage($color_file, $parent->get_color_file());
        
        # apply color lookup table (clut method not implemented by most recent version of MagickWand for PHP)
        MagickAddImage($output_file, $color_file);
        $output_file = MagickFxImage($output_file, "v.p{0,u*v.h}");
        
        # destroy color file, as we don't need it anymore
        DestroyMagickWand($color_file);
        
        # overlay heatmap over source image
        if($parent->get_overlay()) {
            # note: MagickWand PHP code (as of 1.0.8) doesnt export MagickSetImageOpacity by default for some reason
            # see misc/magickwand.patch for a fix
            MagickSetImageOpacity($output_file, ($parent->get_opacity() / 100));
            MagickCompositeImage($input_file, $output_file, MW_OverCompositeOp, 0, 0);
            DestroyMagickWand($output_file);
            $output_file = $input_file;
        }
        
        # write destination image
        MagickWriteImage($output_file, $parent->get_output_file());
        
        # return true if successful
        return True;
    }
    
    protected static function colorize($image_file, $opacity) {
        # reduce percentage to fraction
        $opacity = (100 - $opacity) / 100;
    
        # iterate through each pixel in given image
        $iterator = NewPixelIterator($image_file);
        while($row = PixelGetNextIteratorRow($iterator)) {
            foreach ($row as $pixel) {
                # get and parse rgb values
                $color_string = PixelGetColorAsString($pixel);
                if(!preg_match('/rgb\((?P<r>\d+),(?P<g>\d+),(?P<b>\d+)\)/', $color_string, $colors)) {
                    throw new Exception("Could not parse rgb triplet from '{$color_string}'");
                }
                # colorize each value as necessary
                foreach(array('r', 'g', 'b') as $channel) {
                    $color = $colors[$channel];
                    if($color !== 255) {
                        $colors[$channel] = $color + ((255 - $color) * $opacity);
                    }
                }
                # build rgb triplet string
                PixelSetColor($pixel, "rgb({$colors['r']},{$colors['g']},{$colors['b']})");
            }
            
            # sync iterator after each iteration, to apply changes to original image
            $synced = PixelSyncIterator($iterator);
            if(!$synced) {
                throw new Exception('Failed to sync pixel iterator');
            }
        }
    }
}
