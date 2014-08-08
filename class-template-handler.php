<?php
/**
 * Class for converting PHP templates into static html pages and moving them to desired locations
 * 
 * @param       string  $base       Template base name to look for
 * @param       array   $variations  Specific templates to look for (optional)
 * @property    string  $base       Class variable storing template base name to look for
 * @property    array   $variations Class variable storing specific templates to look for
 * @property    array   $templates  Class variable storing strings representing all templates to process
 */

/******************************
    Sample Usage
    
    $templates = new TemplateHandler( 'login', array( 'sweden', 'norway' ) );
    $templates->php_to_html( dirname( dirname( __FILE__ ) ) . '/static', dirname( __FILE__ ) . '/php-templates' );
    $templates->update_assets( dirname( dirname( __FILE__ ) ) . '/static/assets', dirname( __FILE__ ) . '/php-templates/assets', array('css', 'img', 'js' ) );

 *****************************/

class TemplateHandler {

    private $base, $variations, $templates;

    function __construct( $base, array $variations ) {
        $this->base = $base;
        $this->variations = $variations;
        $this->set_templates();
    }


    /**
    * Set $templates property to array of all templates of this instance
    */
    private function set_templates() {
        $this->templates = array();

        foreach ( (array) $this->variations as $variation ) {
            $variation = strlen( $variation ) > 0 ? '-' . $variation : '';
            $this->templates[] = $this->base . $variation;
        }
    }


    /**
    * Convert all templates to static HTML files, rename files, and move to designated path 
    *
    * @param    string  $output_dir_path    Path in which to place processed html files (defaults to current directory/html)
    * @param    string  $template_path      Path where php templates are located (defaults to current directory)
    * @param    string  $output_filename    Path where php templates are located (defaults to original template name)
    */
    function php_to_html( $output_dir_path = null, $template_path = null, $output_filename = null ) {
        $output_dir_path = $output_dir_path ? $output_dir_path : dirname( __FILE__ ) . '/html';
        $template_path = $template_path ? $template_path : dirname( __FILE__ );

        if ( !file_exists( $output_dir_path ) )
            mkdir( $output_dir_path );

        foreach ( (array) $this->templates as $template ) {
            $filename = $output_filename ? $output_filename : $template;

            ob_start();

            require_once( $template_path . '/' . $template . '.php' );

            if ( ob_get_length() > 0 ) {
                $html = ob_get_contents();
            }

            ob_end_clean();

            file_put_contents( $output_dir_path . '/' . $filename . '.html', $html );

            if ( !isset( $html ) ) {
                echo "error: file $template_path/$template.php can't be read";
            } else {
                echo "<p>File <em>{$template}.php</em> ouput succesfully at <em>" . $output_dir_path . "/{$filename}.html</em></p>";
            }
        }
    }

    /**
    * Move entire directories recursively from one location to another
    *
    * @param    string  $output_dir_path    Directory in which to place processed html files
    * @param    string  $template_path      Directory where php templates are located
    * @param    array   $directories        Folders to copy from one directory to another
    */
    function update_assets( $output_dir_path = null, $template_path = null, $dirs = null ) {
        if ( !$output_dir_path || !$template_path || !$dirs )
            return;

        if ( !file_exists( $output_dir_path ) )
            mkdir( $output_dir_path );

        foreach ( (array) $dirs as $dir ) {
            if ( file_exists( $template_path . '/' . $dir ) ) {
                $this->copy_directory( $template_path . '/' . $dir, $output_dir_path . '/' . $dir );
                echo "<p>Assets at <em>{$output_dir_path}/{$dir}</em> updated successfully</p>";
            }
        }
    }

    /**
    * Move a directory & all of its contents from one location to another
    *
    * @param    string  $src    Source of directory to copy
    * @param    string  $dst    Destination for directory to be copied to
    */
    function copy_directory( $src, $dst ) {
        $dir = opendir( $src );
        @mkdir( $dst );
        while ( false !== ( $file = readdir( $dir )) ) {
            if ( ( $file != '.' ) && ( $file != '..' ) ) {
                if ( is_dir( $src . '/' . $file ) ) {
                    recurse_copy( $src . '/' . $file, $dst . '/' . $file );
                } else {
                    copy( $src . '/' . $file, $dst . '/' . $file );
                }
            }
        }
        closedir( $dir );
    }

}