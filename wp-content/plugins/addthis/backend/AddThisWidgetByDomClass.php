<?php
/**
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2017 AddThis, LLC                                     |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */
require_once 'AddThisFollowButtonsHorizontalTool.php';
require_once 'AddThisFollowButtonsVerticalTool.php';
require_once 'AddThisSharingButtonsFeature.php';

if (!class_exists('AddThisWidgetByDomClass')) {
    /**
     * AddThis' widgets for tools by their id
     *
     * @category   ParentClass
     * @package    AddThisWordPress
     * @subpackage Tools\Widgets
     * @author     AddThis <help@addthis.com>
     * @license    GNU General Public License, version 2
     * @link       http://addthis.com AddThis website
     */
    class AddThisWidgetByDomClass extends WP_Widget
    {
        private $widgetBaseId = 'addthis_tool_by_class_name_widget';
        private $widgetName = 'AddThis Tool';
        private $widgetDescription = 'AddThis Share, Follow and Related Post Tools';

        /**
         * Registers widget with WordPress.
         *
         * @return null
         */
        public function __construct()
        {
            $name = __($this->widgetName, AddThisFeature::$l10n_domain);
            $description = __($this->widgetDescription, AddThisFeature::$l10n_domain);

            $widgetOptions = array(
                'description' => $description,
            );

            $controlOptions = array();

            parent::__construct(
                $this->widgetBaseId,
                $name,
                $widgetOptions,
                $controlOptions
            );
        }

        /**
         * Prints out HTML for the content of the widget
         *
         * @param array $args     Widget arguments
         * @param array $instance Saved values from the database for this
         * instance of the widget
         *
         * @return null
         */
        public function widget($args, $instance)
        {
            $titleHtml = '';
            if (isset($args['before_title'])) {
                $titleHtml = $titleHtml . $args['before_title'];
            }
            if (!empty($instance['title'])) {
                $title = apply_filters(
                    'widget_title',
                    $instance['title'],
                    $instance,
                    $args['widget_id']
                );
                $titleHtml = $titleHtml . $title;
            }
            if (isset($args['after_title'])) {
                $titleHtml = $titleHtml . $args['after_title'];
            }

            if (!empty($instance)) {
                if (empty($instance['share-url'])) {
                    $instance['share-url'] = false;
                }
                if (empty($instance['share-title'])) {
                    $instance['share-title'] = false;
                }
                if (empty($instance['share-description'])) {
                    $instance['share-description'] = false;
                }
                if (empty($instance['share-media'])) {
                    $instance['share-media'] = false;
                }

                $attrString = AddThisSharingButtonsFeature::buildDataAttrString(
                    $instance['share-url'],
                    $instance['share-title'],
                    $instance['share-description'],
                    $instance['share-media']
                );

                if (!empty($instance['conflict'])) {
                    $addThisToolHtml = $this->conflictMode($args, $instance);
                } else {
                    $addThisToolHtml = '<div class="'.$instance['class'].' addthis_tool" '.$attrString.'></div>';
                }
            }

            $globalOptionsObject = new AddThisGlobalOptionsFeature();
            $gooSettings = $globalOptionsObject->getConfigs();
            if (!empty($gooSettings['ajax_support'])) {
                $html .= '<script>if (typeof window.atnt !== \'undefined\') { window.atnt(); }</script>';
            }

            if (!isset($args['widget_name'])) {
                $args['widget_name'] = 'no name';
            }
            if (!isset($args['before_widget'])) {
                $args['before_widget'] = '';
            }
            if (!isset($args['after_widget'])) {
                $args['after_widget'] = '';
            }

            $html  = $args['before_widget'];
            $html .= '<!-- Widget added by an AddThis plugin -->';
            $html .=   '<!-- widget name: ' . $args['widget_name'] . ' -->';
            $html .=   $titleHtml;
            $html .=   $addThisToolHtml;
            $html .= '<!-- End of widget -->';
            $html .= $args['after_widget'];

            echo $html;
        }

        /**
         * Returns HTML and JavaScript for a widget in conflict mode
         *
         * @param array $args     Widget arguments
         * @param array $instance Saved values from the database for this
         * instance of the widget
         *
         * @return string HTML and JavaScript for a widget in conflict mode
         */
        private function conflictMode($args, $instance)
        {
            $html = '';

            if (!empty($instance['layers'])) {
                $layers = $instance['layers'];
                $class = $args['widget_id'];
                foreach ($layers as $toolApiName => $settings) {
                    $layers[$toolApiName]['elements'] = '.'.$class;
                }

                $toolHtml = '<div class="'.$class.'"></div>';

                $layersJson = json_encode((object)$layers);

                $addLayersJavaScript = '<script>';
                $addLayersJavaScript .= '  if (typeof window.addthis_layers_tools === \'undefined\') { ';
                $addLayersJavaScript .= '    window.addthis_layers_tools = ['.$layersJson.']';
                $addLayersJavaScript .= '  } else { ';
                $addLayersJavaScript .= '    window.addthis_layers_tools.push('.$layersJson.');';
                $addLayersJavaScript .= '  }';
                $addLayersJavaScript .= '</script>';
                $html = $toolHtml . $addLayersJavaScript;
            } elseif (!empty($instance['html'])) {
                $html = $instance['html'];
            }

            return $html;
        }

        /**
         * Returns HTML for the title text input field
         *
         * @param array $instance Saved values from the database for this
         * instance of the widget
         *
         * @return string HTML for the title text input
         */
        private function formTitleFieldHtml($instance)
        {
            $titleFieldId = $this->get_field_id('title');
            $titleFieldName = $this->get_field_name('title');
            $titleLabel = esc_html__('Title: ', AddThisFeature::$l10n_domain);

            if (isset($instance['title'])) {
                $titleValue = esc_attr($instance['title']);
            } else {
                $titleValue = '';
            }

            $html = '
                <p>
                    <label
                        for="'.$titleFieldId.'"
                    >
                        '.$titleLabel.'
                    </label>
                    <input
                        class="widefat"
                        id="'.$titleFieldId.'"
                        name="'.$titleFieldName.'"
                        type="text"
                        value="'.$titleValue.'"
                    />
                </p>
            ';

            return $html;
        }

        /**
         * Returns HTML for the dropdown list of available tools
         *
         * @param array $tools    associated array of CSS classes for tools as keys
         * and pretty human language names for tools as values for enabled tools
         * @param array $instance Saved values from the database for this
         * instance of the widget
         *
         * @return string HTML for the class/tool select field
         */
        private function formClassFieldHtml($tools, $instance)
        {
            $classFieldId = $this->get_field_id('class');
            $classFieldName = $this->get_field_name('class');
            $classLabel = esc_html__('Tool: ', AddThisFeature::$l10n_domain);

            if (isset($instance['class'])) {
                $classValue = esc_attr($instance['class']);
            } else {
                $classValue = '';
            }

            $errorHtml = '';

            if (!empty($classValue) && !isset($tools[$classValue])) {
                $tools[$classValue] = $classValue;

                $errorTemplate = 'Error! The tool with id %s does not exist in your settings. Please select another tool.';
                $errorTemplate = esc_html__($errorTemplate, AddThisFeature::$l10n_domain);

                $errorMessage = sprintf($errorTemplate, $classValue);

                $errorHtml = '
                <p>
                    <strong>
                        '.$errorMessage.'
                    </strong>
                    <br />
                </p>
                ';
            }

            $selectOptions = '';
            foreach ($tools as $element => $toolName) {
                if ($classValue === $element) {
                    $selected = 'selected="selected"';
                } else {
                    $selected = '';
                }
                $selectOptions .= '<option value="'.$element.'" '.$selected.'>'.$toolName.'</option>\n';
            }

            $html = '
                <p>
                    <label
                        for="'.$classFieldId.'"
                    >
                        '.$classLabel.'
                    </label>
                    <select
                        id="'.$classFieldId.'"
                        name="'.$classFieldName.'"
                    >
                        '.$selectOptions.'
                    </select>
                </p>
                ' .$errorHtml;

            return $html;
        }
        /**
         * Returns a list of the enabled tools from WordPress settings
         *
         * @param array $goo AddThis Global Options object
         *
         * @return array associated array of CSS classes for tools as keys and
         * pretty language names for tools as values
         */
        private function availableToolsAnonymous($goo)
        {
            $tools = array();
            $configs = $goo->getConfigs();

            if (!empty($configs['follow_buttons_feature_enabled'])) {
                // figure out inline tools from WordPress settings
                $classes = array(
                    'AddThisFollowButtonsHorizontalTool',
                    'AddThisFollowButtonsVerticalTool'
                );

                foreach ($classes as $class) {
                    $toolObject = new $class();
                    $toolObject->getFeatureConfigs();

                    $toolConfigs = $toolObject->getToolConfigs();

                    if ($toolObject->isEnabled() &&
                        empty($toolConfigs['conflict'])
                    ) {
                        // only list enabled tools without conflicts
                        $tools[$toolObject->layersClass] = esc_html__(
                            $toolObject->prettyName,
                            AddThisFeature::$l10n_domain
                        );
                    }
                    unset($toolObject);
                }
            }

            if (!empty($configs['sharing_buttons_feature_enabled'])) {
                $shareButtonsFeatureObject = new AddThisSharingButtonsFeature();
                $shareButtonConfigs = $shareButtonsFeatureObject->getConfigs();
                if (is_array($shareButtonConfigs)) {
                    foreach ($shareButtonConfigs as $toolSettings) {
                        if (!empty($toolSettings['enabled']) &&
                            !empty($toolSettings['elements']) &&
                            is_array($toolSettings['elements'])
                        ) {
                            reset($toolSettings['elements']);
                            $firstElement = trim($toolSettings['elements'][key($toolSettings['elements'])]);
                            if (substr($firstElement, 0, 1) === '.') {
                                $firstElement = substr($firstElement, 1);
                            }

                            if (!empty($toolSettings['toolName'])) {
                                $toolName = $toolSettings['toolName'];
                            } else {
                                switch ($toolSettings['id']) {
                                    case 'shin':
                                        $toolName = 'Share Buttons';
                                        break;
                                    case 'html':
                                        $toolName = 'Custom HTML';
                                        break;
                                    default:
                                        $toolName = 'Unknown Inline Tool';
                                        break;
                                }
                            }

                            $tools[$firstElement] = esc_html__(
                                $toolName,
                                AddThisFeature::$l10n_domain
                            );
                        }
                    }
                }
            }

            return $tools;
        }

        /**
         * Returns a list of the enabled tools on the AddThis profile ID used
         *
         * @param array $boost AddThis settings for profile ID
         *
         * @return array associated array of CSS classes for tools as keys and
         * pretty language names for tools as values
         */
        private function availableToolsRegistered($boost)
        {
            $tools = array();
            $trackDuplicate = array('class' => array(), 'name' => array());

            if (isset($boost['templates'][0]['widgets'])
                && is_array($boost['templates'][0]['widgets'])
            ) {
                foreach ($boost['templates'][0]['widgets'] as $tool) {
                    if (!empty($tool['enabled']) && !empty($tool['elements'])) {
                        $allElements = explode(',', $tool['elements']);
                        $firstElement = trim($allElements[0]);
                        if (substr($firstElement, 0, 1) === '.') {
                            $firstElement = substr($firstElement, 1);
                        }

                        if (isset($tool['toolName'])) {
                            $toolName = $tool['toolName'];
                        } else {
                            switch ($tool['id']) {
                                case 'shin':
                                    $toolName = 'Sharing Buttons';
                                    if (isset($tool['thirdPartyButtons']) && $tool['thirdPartyButtons']) {
                                        $toolName = 'Original ' . $toolName;
                                    }
                                    break;
                                case 'flwi':
                                    $toolName = 'Follow Buttons';
                                    if (isset($tool['orientation'])) {
                                        $toolName = ucfirst($tool['orientation']) . ' ' . $toolName;
                                    } else {
                                        $toolName = 'Custom ' . $toolName;
                                    }
                                    break;
                                case 'rpin':
                                    $toolName = 'Related Posts';
                                    if (isset($tool['orientation'])) {
                                        $toolName = ucfirst($tool['orientation']) . ' ' . $toolName;
                                    }
                                    break;
                                default:
                                    $toolName = 'Inline';
                            }
                        }

                        if (!isset($toolName)) {
                            $toolName = $firstElement;
                        }

                        $tmpToolName = $toolName;
                        $toolNameCount = 1;
                        while (in_array($tmpToolName, $trackDuplicate['name'])) {
                            $toolNameCount = $toolNameCount + 1;
                            $tmpToolName = $toolName . ' (' . $toolNameCount . ')';
                        }
                        $trackDuplicate['name'][] = $tmpToolName;
                        $toolName = $tmpToolName;

                        if (!in_array($firstElement, $trackDuplicate['class'])) {
                            $trackDuplicate['class'][] = $firstElement;
                            $tools[$firstElement] = $toolName;
                        }

                        unset($toolName);
                    }
                }
            }

            return $tools;
        }

        /**
         * Prints out HTML for the options form in the WordPress admin Dashboard
         *
         * @param array $instance Saved values from the database for this
         * instance of the widget
         *
         * @return null
         */
        public function form($instance)
        {
            $titleHtml = $this->formTitleFieldHtml($instance);

            $goToolObject = new AddThisGlobalOptionsTool();
            $goo = $goToolObject->getGlobalOptionsObject();

            $profileId = $goo->getProfileId();

            if (!empty($profileId) && $goo->inRegisteredMode()) {
                $boost = $goo->getBoostConfig();
                $tools = $this->availableToolsRegistered($boost);
                $classHtml = $this->formClassFieldHtml($tools, $instance);
            } elseif (empty($instance['conflict'])) {
                $tools = $this->availableToolsAnonymous($goo);
                $classHtml = $this->formClassFieldHtml($tools, $instance);
            }

            if (is_array($tools) && empty($tools)) {
                $titleHtml = '';
                $activateText = 'No active inline tools yet. Go activate some!';
                $activateText = esc_html__($activateText, AddThisFeature::$l10n_domain);
                $classHtml = '<p><strong>'.$activateText.'</strong></p>';
            } elseif ($goo->inAnonymousMode() && !empty($instance['conflict'])) {
                $settingsText = esc_html__('the plugin\'s settings', AddThisFeature::$l10n_domain);
                $settingsLink = '<a href="'.$url.'">'.$settingsText.'</a>';

                $conflictTemplate = 'Uh oh! We couldn\'t automatically upgrade this widget. This widget still works, but if you would like to change its configuration please delete it, go to %1$s, update your tool settings, then come back here to add an a new AddThis Tool widget.';
                $conflictTemplate = esc_html__($conflictTemplate, AddThisFeature::$l10n_domain);
                $conflictText = sprintf($conflictTemplate, $settingsLink);

                $titleLabel = esc_html__('Title: ', AddThisFeature::$l10n_domain);
                if (isset($instance['title'])) {
                    $titleValue = esc_attr($instance['title']);
                } else {
                    $titleValue = '';
                }

                $titleHtml = $titleLabel . $titleValue;
                $classHtml = '<p><strong>'.$conflictText.'</strong><br /></p>';
            }

            $html = $titleHtml . $classHtml . '
                <p>'.$goo->getSettingsLinkHtmlForWidgets().'</p>
                <p>'.$goo->eulaText('Save').'</p>
            ';

            echo $html;
        }

        /**
         * Processing widget options on save
         *
         * @param array $new_instance options values just sent to be saved
         * @param array $old_instance previously options values (from database)
         *
         * @return array
         */
        public function update($new_instance, $old_instance)
        {
            if (isset($old_instance['conflict'])) {
                return $old_instance;
            }

            $instance = $old_instance;

            if (isset($new_instance['title'])) {
                $instance['title'] = strip_tags($new_instance['title']);
            } else {
                $titleDefault = $this->toolsClass->defaultWidgetTitle;
                $titleValue = esc_html__($titleDefault, AddThisFeature::$l10n_domain);
                $instance['title'] = $titleDefault;
            }

            if (isset($new_instance['class'])) {
                $instance['class'] = strip_tags($new_instance['class']);
            }

            return $instance;
        }
    }
}