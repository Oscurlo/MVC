<?php

namespace Model;

use Exception;
use Intervention\Image\ImageManagerStatic as Image;
use System\Config\AppConfig;

class ImageProcessor
{
    # Optimize images based on the provided format and quality
    static function optimizeImages(String|array $format, $quality): void
    {
        # Check if the GD extension is loaded
        if (extension_loaded("GD")) {
            # Get image paths based on the provided format
            $imagePaths = strtoupper(gettype($format)) == "STRING" ? glob($format) : $format;

            # Valid image file extensions
            $validExt = ["jpg", "png", "gif", "tif", "bmp", "ico", "psd", "webp"];

            # Iterate through image paths
            foreach ($imagePaths as $imagePath) {
                # Check if the image file exists
                if (file_exists($imagePath)) {
                    # Get file information
                    $info = pathinfo($imagePath);

                    # Check if the file has a valid extension
                    if (isset($info["extension"]) && in_array(strtolower($info["extension"]), $validExt)) {
                        # Optimize and encode the image with specified quality
                        self::encodeImage($imagePath, $quality, $info["extension"]);
                    }
                }
            }
        } else {
            # GD extension is not enabled
            throw new Exception("Enable the \"GD\" extension");
        }
    }

    # Encode an image with specified quality and output format
    static function encodeImage($img, $quality, $outputFormat = "data-url"): void
    {
        # Use Intervention Image library to encode and save the image
        Image::make($img)->encode($outputFormat, $quality)->save($img);
    }

    # Resize an image to the specified dimensions
    static function resizeImage($img, $newWidth, $newHeight): void
    {
        # Use Intervention Image library to resize and save the image
        Image::make($img)->resize($newWidth, $newHeight)->save($img);
    }

    # Rotate an image by a specified angle
    static function rotateImage($img, $angle): void
    {
        # Use Intervention Image library to rotate and save the image
        Image::make($img)->rotate($angle)->save($img);
    }

    # Correct image URL by removing base folder and server information
    static function correctImageURL(String $url): ?String
    {
        # Remove base folder and server information from the URL
        $url = str_replace([
            AppConfig::BASE_FOLDER,
            AppConfig::BASE_SERVER
        ], "", $url);

        # Trim leading and trailing slashes
        $img = trim($url, "/");

        # Create full paths for folder and server
        $pathFolder = AppConfig::BASE_FOLDER . "/" . $img;
        $pathServer = AppConfig::BASE_SERVER . "/" . $img;

        # Return the corrected image URL if the file exists, otherwise return null
        return (file_exists($pathFolder) ? $pathServer : null);
    }
}
