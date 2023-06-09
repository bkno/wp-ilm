<?php

namespace ACP\Column\CustomField;

use AC\Helper\Select\Option;
use AC\Settings\Column\CustomFieldType;
use AC\Type\ToggleOptions;
use ACP\ApplyFilter\CustomField\StoredDateFormat;
use ACP\Column;
use ACP\Editing;
use ACP\Editing\PaginatedOptions;
use ACP\Editing\Service;
use ACP\Editing\Settings;
use ACP\Editing\View;
use ACP\Settings\Column\SerializedArray;

class EditingModelFactory {

	public static function unsupported_field_types() {
		return [
			CustomFieldType::TYPE_COUNT,
			CustomFieldType::TYPE_NON_EMPTY,
		];
	}

	/**
	 * @param string             $field_type
	 * @param Editing\Storage    $storage
	 * @param Column\CustomField $column
	 *
	 * @return Service|false
	 */
	public static function create( $field_type, Editing\Storage $storage, Column\CustomField $column ) {
		$unsupported_field_types = self::unsupported_field_types();

		if ( in_array( $field_type, $unsupported_field_types ) ) {
			return false;
		}

		switch ( $field_type ) {
			case CustomFieldType::TYPE_ARRAY :
				if ( ! $storage instanceof Editing\Storage\Meta ) {
					return false;
				}

				return new Service\SerializedMeta( $storage, self::get_serialized_keys( $column ) );
			case CustomFieldType::TYPE_BOOLEAN :
				return new Service\Basic(
					new View\Toggle(
						new ToggleOptions(
							new Option( '0', __( 'False', 'codepress-admin-columns' ) ),
							new Option( '1', __( 'True', 'codepress-admin-columns' ) )
						)
					),
					$storage
				);
			case CustomFieldType::TYPE_COLOR :
				return new Service\Basic( ( new View\Color() )->set_clear_button( true ), $storage );
			case CustomFieldType::TYPE_DATE :
				$date_format = ( new StoredDateFormat( $column ) )->apply_filters( 'Y-m-d' );

				return new Service\Date( ( new View\Date() )->set_clear_button( true ), $storage, $date_format );
			case CustomFieldType::TYPE_IMAGE :
				return new Service\Basic( ( new View\Image() )->set_clear_button( true ), $storage );
			case CustomFieldType::TYPE_MEDIA :
				return new Service\Basic( ( new View\Media() )->set_multiple( true )->set_clear_button( true ), $storage );
			case CustomFieldType::TYPE_URL :
				return new Service\Basic( ( new View\InternalLink() )->set_clear_button( true )->set_placeholder( __( 'Paste URL or type to search' ) ), $storage );
			case CustomFieldType::TYPE_NUMERIC :
				return new Service\ComputedNumber( $storage );
			case CustomFieldType::TYPE_POST :
				$post_types = (array) apply_filters( 'acp/editing/settings/post_types', [], $column );

				return new Service\Posts(
					( new View\AjaxSelect() )->set_clear_button( true ),
					$storage,
					new PaginatedOptions\Posts( $post_types )
				);
			case CustomFieldType::TYPE_USER :
				return new Service\User(
					( new View\AjaxSelect() )->set_clear_button( true ),
					$storage,
					new PaginatedOptions\Users()
				);
			default :
				$type = self::get_editable_type( $column ) ?: 'textarea';

				switch ( $type ) {
					case Settings\EditableType\Text::TYPE_WYSIWYG:
						return new Service\Basic( ( new View\Wysiwyg() )->set_clear_button( true ), $storage );
					case Settings\EditableType\Text::TYPE_TEXTAREA:
						return new Service\Basic( ( new View\TextArea() )->set_clear_button( true ), $storage );
					case Settings\EditableType\Text::TYPE_TEXT:
					default:
						return new Service\Basic( ( new View\Text() )->set_clear_button( true ), $storage );
				}
		}
	}

	private static function get_serialized_keys( Column\CustomField $column ) {
		$setting = $column->get_setting( 'array_keys' );

		return $setting instanceof SerializedArray
			? $setting->get_keys()
			: [];
	}

	private static function get_editable_type( Column\CustomField $column ) {
		$setting = $column->get_setting( 'edit' );

		if ( $setting instanceof Settings ) {
			$editable_type = $setting->get_section( Settings\EditableType::NAME );
			if ( $editable_type instanceof Settings\EditableType ) {
				return $editable_type->get_editable_type();
			}
		}

		return null;
	}

}