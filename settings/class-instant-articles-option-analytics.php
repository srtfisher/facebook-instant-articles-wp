<?php
/**
 * Facebook Instant Articles for WP.
 * This source code is licensed under the license found in the
 * LICENSE file in the root directory of this source tree.
 *
 * @package default
 */

require_once( dirname( __FILE__ ) . '/class-instant-articles-option.php' );

/**
 * Analytics configuration class.
 */
class Instant_Articles_Option_Analytics extends Instant_Articles_Option {

	const OPTION_KEY = IA_PLUGIN_TEXT_DOMAIN . '-option-analytics';

	const SECTIONS = array(
		'title' => 'Analytics',
		'description' => 'This is where you configure your analytics settings. If you already use a Wordpress Plugin to manage your analytics, look for it in <strong>3rd Party Integrations</strong>.',
	);

	const FIELDS = array(

		'integrations' => array(
			'label' => '3rd party integrations',
			'render' => array( 'Instant_Articles_Option_Analytics', 'custom_render_integrations' ),
			'default' => [],
		),

		'embed_code_enabled' => array(
			'label' => 'Custom tracker',
			'render' => 'checkbox',
			'default' => false,
			'checkbox_label' => 'Enable custom tracker code',
		),

		'embed_code' => array(
			'label' => 'Custom tracker code',
			'render' => 'textarea',
			'placeholder' => '<script>...</script>',
			'default' => '',
		),
	);

	/**
	 * Constructor.
	 *
	 * @since 0.4
	 */
	public function __construct() {
		parent::__construct(
			self::OPTION_KEY,
			self::SECTIONS,
			self::FIELDS
		);
		wp_localize_script( 'instant-articles-option-analytics', 'INSTANT_ARTICLES_OPTION_ANALYTICS', array(
			'option_field_id_embed_code_enabled' => self::OPTION_KEY . '-embed_code_enabled',
			'option_field_id_embed_code'         => self::OPTION_KEY . '-embed_code',
		) );
	}

	/**
	 * Renders the markup for the `integrations` field.
	 *
	 * @param array $args The array with configuration of fields.
	 * @since 0.4
	 */
	public static function custom_render_integrations( $args ) {
		$name = $args['serialized_with_group'] . '[integrations][]';

		$compat_plugins = parent::get_registered_compat( 'instant_articles_compat_registry_analytics' );

		if ( empty( $compat_plugins ) ) {
			?>
			<em>
				<?php echo esc_html( 'No supported analytics plugins are installed nor activated' ); ?>
			</em>
			<?php

			return;
		}

		asort( $compat_plugins );
		foreach ( $compat_plugins as $plugin_id => $plugin_info ) {
			?>
			<label>
				<input
					type="checkbox"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( $plugin_id ); ?>"
					<?php echo checked( in_array( $plugin_id, self::$settings['integrations'], true ) ) ?>
				>
				<?php echo esc_html( $plugin_info['name'] ); ?>
			</label>
			<br />
			<?php
		}
	}

	/**
	 * Sanitize and return all the field values.
	 *
	 * This method receives a payload containing all value for its fields and
	 * should return the same payload after having been sanitized.
	 *
	 * Do not encode the payload as this is performed by the
	 * universal_sanitize_and_encode_handler() of the parent class.
	 *
	 * @param array $field_values The values in an array mapped keys.
	 * @since 0.5
	 */
	public function sanitize_option_fields( $field_values ) {
		foreach ( $field_values as $field_id => $field_value ) {
			$field = self::FIELDS[ $field_id ];

			switch ( $field_id ) {
				case 'embed_code':
					if ( isset( $field_values['embed_code_enabled'] ) && $field_values['embed_code_enabled'] ) {
						$document = new DOMDocument();
						$fragment = $document->createDocumentFragment();
						if ( ! @$fragment->appendXML( $field_values[ $field_id ] ) ) {
							add_settings_error(
								'embed_code',
								'invalid_markup',
								'Invalid HTML markup provided for custom analytics tracker code'
							);
						}
					}

				break;
			}
		}

		return $field_values;
	}
}
