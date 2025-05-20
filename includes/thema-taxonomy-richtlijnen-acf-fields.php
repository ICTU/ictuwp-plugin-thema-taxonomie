<?php
/**
 * GC ACF Fields for: Thema Taxonomy (richtlijnen fields)
 *
 * ACF fields for `thema` taxonomy
 *
 * @see https://www.advancedcustomfields.com/resources/register-fields-via-php/
 *
 * - It is important to remember that each field group’s key and each field’s key must be unique.
 * The key is a reference for ACF to find, save and load data. If 2 fields or 2 groups are added using
 * the same key, the later will override the original.
 *
 * - Field Groups and Fields registered via code will NOT be visible/editable via
 * the “Edit Field Groups” admin page.
 *
 * Initialize with eg:
 * add_action('acf/init', 'my_acf_add_local_field_groups');
 *
 */

// Add the field group: 'Metabox: (45) Thema Richtlijnen tonen'
// definitions taken from:
// [themes]/ictuwp-theme-gc2020/acf-json/group_??.json.original
//
// Metabox order for template-thema-detail.php
// this number determines $menu_order
//
// 00 - Inleiding
// ?? - Video's tonen
// ?? - Podcasts tonen
// ?? - Instrumenten tonen
// ?? - Community's tonen
// 30 - Events
// 40 - berichten
// 45 - richtlijnen <--
// ----------------------------
if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

acf_add_local_field_group( array(
	'key' => 'group_66ec33c5cdac1',
	'title' => 'Metabox: (45) Thema Richtlijnen tonen',
	'fields' => array(
		array(
			'key' => 'field_66ec33c6c0ec9',
			'label' => 'Gerelateerde richtlijnen',
			'name' => 'richtlijnen',
			'aria-label' => '',
			'type' => 'group',
			'instructions' => 'Toon of verberg het blok met richtlijnen voor deze pagina.',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'layout' => 'block',
			'sub_fields' => array(
				array(
					'key' => 'field_66ec33c6c2467',
					'label' => 'Richtlijnen tonen voor dit thema?',
					'name' => 'metabox_thema_richtlijnen_show_or_not',
					'aria-label' => '',
					'type' => 'radio',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'ja' => 'Ja',
						'nee' => 'Nee',
					),
					'default_value' => 'nee',
					'return_format' => 'value',
					'allow_null' => 0,
					'other_choice' => 0,
					'allow_in_bindings' => 1,
					'layout' => 'horizontal',
					'save_other_choice' => 0,
				),
				array(
					'key' => 'field_66ec33c6c246f',
					'label' => 'Titel',
					'name' => 'metabox_thema_richtlijnen_titel',
					'aria-label' => '',
					'type' => 'text',
					'instructions' => 'Laat dit veld niet leeg, alsjeblieft. Standaard-tekst is "Hulp nodig?"',
					'required' => 1,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_66ec33c6c2467',
								'operator' => '==',
								'value' => 'ja',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => _x( 'Hulp nodig?', 'default title for richtlijnen', 'ictuwp-plugin-thema-taxonomie' ),
					'maxlength' => '',
					'allow_in_bindings' => 1,
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
				),
				array(
					'key' => 'field_66ec33c6c2476',
					'label' => 'Omschrijving',
					'name' => 'metabox_thema_richtlijnen_omschrijving',
					'aria-label' => '',
					'type' => 'textarea',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_66ec33c6c2467',
								'operator' => '==',
								'value' => 'ja',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'default_value' => '',
					'maxlength' => '',
					'allow_in_bindings' => 0,
					'rows' => 4,
					'placeholder' => '',
					'new_lines' => 'wpautop',
				),
				array(
					'key' => 'field_66f52165208a1',
					'label' => 'Selecteer richtlijnen',
					'name' => 'metabox_thema_richtlijnen_select',
					'aria-label' => '',
					'type' => 'taxonomy',
					'instructions' => '',
					'required' => 1,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_66ec33c6c2467',
								'operator' => '==',
								'value' => 'ja',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'taxonomy' => 'richtlijn',
					'add_term' => 0,
					'save_terms' => 0,
					'load_terms' => 0,
					'return_format' => 'object',
					'field_type' => 'multi_select',
					'allow_null' => 0,
					'allow_in_bindings' => 0,
					'bidirectional' => 0,
					'multiple' => 0,
					'bidirectional_target' => array(
					),
				),
				array(
					'key' => 'field_66ec33c6c247d',
					'label' => 'Overzichtslink',
					'name' => 'metabox_thema_richtlijnen_url_overview',
					'aria-label' => '',
					'type' => 'link',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_66ec33c6c2467',
								'operator' => '==',
								'value' => 'ja',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'return_format' => 'array',
					'allow_in_bindings' => 1,
				),
				array(
					'key' => 'field_66ec33c6c2483',
					'label' => 'Sectie stijl',
					'name' => 'metabox_thema_richtlijnen_section_style',
					'aria-label' => '',
					'type' => 'radio',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => array(
						array(
							array(
								'field' => 'field_66ec33c6c2467',
								'operator' => '==',
								'value' => 'ja',
							),
						),
					),
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'choices' => array(
						'default' => 'Standaard',
						'background' => 'Met achtergrond',
					),
					'default_value' => 'default',
					'return_format' => 'value',
					'allow_null' => 0,
					'other_choice' => 0,
					'allow_in_bindings' => 0,
					'layout' => 'vertical',
					'save_other_choice' => 0,
				),
			),
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'page_template',
				'operator' => '==',
				'value' => 'template-thema-detail.php',
			),
		),
	),
	'menu_order' => 40,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
) );
// ----------------------------
