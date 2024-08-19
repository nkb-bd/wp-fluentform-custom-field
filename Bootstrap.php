<?php

/**
 * Class FFConfirmField
 * Example Custom Field For WP FluentForms
 * Set a target field name and this field will work as Confirmation field
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use FluentForm\App\Modules\Form\FormFieldsParser;
use \FluentForm\Framework\Helpers\ArrayHelper;

class FFConfirmField extends \FluentForm\App\Services\FormBuilder\BaseFieldManager
{
    public function __construct()
    {
        parent::__construct(
            'confirm_field',
            'Confirm Input',
            ['confirm', 'check'],
            'general'
        );
        
        add_filter("fluentform/validate_input_item_{$this->key}", [$this, 'validate'], 10, 5);
        
        $this->hideFieldFormEntries();
    }
    
    public function getComponent()
    {
        return [
            'index'          => 16,
            'element'        => $this->key,
            'attributes'     => [
                'name'        => $this->key,
                'class'       => '',
                'value'       => '',
                'type'        => 'text',
                'placeholder' => __('Confirm Input', 'fluentformpro')
            ],
            'settings'       => [
                'container_class'     => '',
                'placeholder'         => '',
                'auto_select_country' => 'no',
                'label'               => $this->title,
                'label_placement'     => '',
                'help_message'        => '',
                'validate_on_change'  => false,
                'target_input'        => '',
                'error_message'       => __('Confirm value does not match', 'fluentformpro'),
                'validation_rules'    => [
                    'required' => [
                        'value'   => false,
                        'message' => __('This field is required', 'fluentformpro'),
                    ]
                ],
                'conditional_logics'  => []
            ],
            'editor_options' => [
                'title'      => $this->title . ' Field',
                'icon_class' => 'el-icon-phone-outline',
                'template'   => 'inputText'
            ],
        ];
    }
    
    public function getGeneralEditorElements()
    {
        return [
            'label',
            'placeholder',
            'value',
            'label_placement',
            'validation_rules',
        ];
    }
    
    public function generalEditorElement()
    {
        return [
            'target_input'       => [
                'template'  => 'inputText',
                'label'     => 'Target Field Name',
                'help_text' => 'The input value will be matched with target input and show error if not matched',
            ],
            'error_message'      => [
                'template' => 'inputText',
                'label'    => 'Error Message',
            ],
            'validate_on_change' => [
                'template' => 'inputCheckbox',
                'label'    => 'Validate on Change',
                'options'  => array(
                    array(
                        'value' => true,
                        'label' => __('Yes', 'fluentform'),
                    ),
                )
            ],
        ];
    }
    
    public function validate($errorMessage, $field, $formData, $fields, $form)
    {
        $ConfirmInputName = ArrayHelper::get($field, 'raw.attributes.name');
        $targetInputName = ArrayHelper::get($field, 'raw.settings.target_input');
        $message = ArrayHelper::get($field, 'raw.settings.error_message');
        
        if (ArrayHelper::get($formData, $ConfirmInputName) != ArrayHelper::get($formData, $targetInputName)) {
            $errorMessage = [$message];
        }
        
        return $errorMessage;
    }
    
    public function render($data, $form)
    {
        $data['attributes']['id'] = $this->makeElementId($data, $form);
        $this->pushScripts($data, $form);
        return (new FluentForm\App\Services\FormBuilder\Components\Text())->compile($data, $form);
    }
    
    private function pushScripts($data, $form)
    {
        add_action('wp_footer', function () use ($data, $form) {
            if (!ArrayHelper::isTrue($data, 'settings.validate_on_change')) {
                return;
            }
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    function confirmValidate() {

                        let confirmInput = jQuery('.<?php echo $form->instance_css_class; ?>').find("#<?php echo $data['attributes']['id']; ?>");
                        let targetName = '<?php echo ArrayHelper::get($data, 'settings.target_input') ?>';
                        let message = '<?php echo ArrayHelper::get($data, 'settings.error_message') ?>';
                        let targetInput = jQuery("input[name='" + targetName + "']")
                        let timeout = null;
                        confirmInput.on("keyup", function () {
                            clearTimeout(timeout); // this will clear the recursive unneccessary calls
                            timeout = setTimeout(() => {
                                validate()
                            }, 1500);
                        });

                        function validate() {
                            if (confirmInput.val() !== targetInput.val()) {
                                let div = $('<div/>', {class: 'error text-danger'});
                                confirmInput.closest('.ff-el-group').addClass('ff-el-is-error');
                                confirmInput.closest('.ff-el-input--content').find('div.error').remove();
                                confirmInput.closest('.ff-el-input--content').append(div.text(message));
                            } else {
                                confirmInput.closest('.ff-el-group').removeClass('ff-el-is-error');
                                confirmInput.closest('.ff-el-input--content').find('div.error').remove();
                            }
                        }
                    }

                    confirmValidate();
                });
            </script>
            <?php
        }, 9999);
    }
    
    private function hideFieldFormEntries()
    {
        add_filter('fluentform/all_entry_labels', function ($formLabels, $form_id) {
            $form = wpFluent()->table('fluentform_forms')->find($form_id);
            $confirmField = FormFieldsParser::getInputsByElementTypes($form, ['confirm_field']);
            if (is_array($confirmField) && !empty($confirmField)) {
                ArrayHelper::forget($formLabels, array_keys($confirmField));
            }
            return $formLabels;
        }, 10, 2);
        
        add_filter('fluentform/all_entry_labels_with_payment', function ($formLabels, $test, $form) {
            $confirmField = FormFieldsParser::getInputsByElementTypes($form, ['confirm_field']);
            if (is_array($confirmField) && !empty($confirmField)) {
                ArrayHelper::forget($formLabels, array_keys($confirmField));
            }
            return $formLabels;
        }, 10, 3);
    }
    
}


