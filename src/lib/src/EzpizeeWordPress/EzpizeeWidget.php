<?php

namespace EzpizeeWordPress;

use WP_Widget;

class EzpizeeWidget extends WP_Widget
{
    public function __construct()
    {
        load_plugin_textdomain( 'ezpizee' );

        parent::__construct(
            'ezpizee_widget',
            __( 'Ezpizee Widget' , 'ezpizee'),
            array( 'description' => __( 'Display the number of spam comments Ezpizee has caught' , 'ezpizee') )
        );

        if ( is_active_widget( false, false, $this->id_base ) ) {
            add_action( 'wp_head', array( $this, 'css' ) );
        }
    }

    public function css(): void
    {
    }

    public function form($instance): string
    {
        return parent::form($instance);
    }

    public function update( $new_instance, $old_instance ): array
    {
        return parent::update($new_instance, $old_instance);
    }

    public function widget( $args, $instance ): void
    {
        die('TODO');
    }

    public static function register(): void
    {
        register_widget('\EzpizeeWordPress\EzpizeeWidget');
    }
}