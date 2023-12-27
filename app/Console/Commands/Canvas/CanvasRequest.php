<?php
/**
 * Created by PhpStorm.
 * User: rajneeshgautam
 * Date: 09/02/22
 * Time: 5:30 PM
 */

namespace App\Console\Commands\Canvas;


class CanvasRequest
{
    public $account_id = 43; // University Canvas
    public $course_id;
    public $module_id;
    public $query_params = [];
    public $user_id;
    public $event; // conclude|delete|undelete

    public $per_page = 100;
    public $page_number = 1;
    public $search_term;
    public $form_params;
    public $include = [];

    public function getFormParams(){
        //dd($this->include);
        $form_params = [
            'per_page' => $this->per_page,
            'page' => $this->page_number,
            'search_term' => $this->search_term,
        ];

        if(count($this->query_params) > 0){
            $form_params = array_merge($this->query_params, $form_params);
        }
        $url = http_build_query(array_filter($form_params));
        foreach ($this->include as $item) {
            $url .= '&include[]='.$item;
            //$form_params['include'][''] = $item;
        }
        return $url;
    }
}