<?php
/**
 * @class ParameterController
 *
 * This class is the controller for the Parameter objects.
 *
 * @author Leano Martinet <info@asterion-cms.com>
 * @package Asterion\App\Helpers
 * @version 4.0.0
 */
class Parameter_Controller extends Controller
{

    public function listAdminItems()
    {
        $html = '';
        $parameters = (new Parameter)->readList(['order' => 'code']);
        $types = [
            'meta' => [
                'title' => __('meta_informations'),
                'description' => __('meta_informations_parameters_description'),
            ],
            'email' => [
                'title' => __('emails'),
                'description' => __('emails_parameters_description'),
            ],
            'mailer' => [
                'title' => __('mailer'),
                'description' => __('mailer_parameters_description'),
            ],
            'link' => [
                'title' => __('links'),
                'description' => __('links_parameters_description'),
            ],
            'map' => [
                'title' => __('maps_information'),
                'description' => __('maps_information_parameters_description'),
            ],
            'others' => [
                'title' => __('others'),
            ],
        ];

        foreach ($types as $key => $type) {
            $types[$key]['items'] = [];
            foreach ($parameters as $parameter) {
                $parameterType = explode('_', $parameter->get('code'))[0];
                if ($key != 'others') {
                    if ($parameterType == $key) {
                        $types[$key]['items'][] = $parameter;
                    }
                } else {
                    if (!in_array($parameterType, ['meta', 'email', 'link', 'map'])) {
                        $types[$key]['items'][] = $parameter;
                    }
                }
            }
            if (count($types[$key]['items']) > 0) {
                $parametersHtml = '';
                foreach ($types[$key]['items'] as $parameter) {
                    $parametersHtml .= $parameter->showUi('Admin');
                }
                $html .= '
                    <div class="block">
                        <div class="block_title">' . $types[$key]['title'] . '</div>
                        ' . ((isset($types[$key]['description'])) ? '
                        <div class="block_description">' . $types[$key]['description'] . '</div>
                        ' : '') . '
                        <div class="block_items">' . $parametersHtml . '</div>
                    </div>';
            }
        }

        return '
            <div class="list_items list_items reload_list_items" data-url="' . url($this->type . '/list_items', true) . '">
                ' . $html . '
            </div>';
    }

}
