<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// html_form.class.php
// version date: March 2010

class Filter extends CHTML {
    
    public function getOptionList($rows,$column_caption,$column_value) {
        $option_list = array();
        foreach($rows as $row) {
            $option_list[$row[$column_value]] = $row[$column_caption];
        }
        return $option_list;
    }
    
    public function select_equal($name,$value = '',$attributes=array(), $option_list = array()) {
        if(!empty($option_list)) {
            $html = parent::addSelectList('filter[EQ][' . $name . ']', $option_list, true, $value, "", $attributes);
        } else {
            $html = parent::addLabel('Invalid Select');
        }
        return $html;
    }
    
    public function text_like_both($name,$value = '',$attributes=array()) {
        $html = parent::addInput('text', 'filter[LKB][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function text_like_front($name,$value = '',$attributes=array()) {
        $html = parent::addInput('text', 'filter[LKF][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function text_like_end($name,$value = '',$attributes=array()) {
        $html = parent::addInput('text', 'filter[LKE][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function text_equal($name,$value = '',$attributes=array()) {
        $html = parent::addInput('text', 'filter[EQ][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function text_greater_equal($name,$value = '',$attributes=array()) {
        $html = parent::addInput('text', 'filter[GTE][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function text_greater($name,$value = '',$attributes=array()) {
        $html = parent::addInput('text', 'filter[GT][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function text_less_equal($name,$value = '',$attributes=array()) {
        $html = parent::addInput('text', 'filter[LTE][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function text_less($name,$value = '',$attributes=array()) {
        $html = parent::addInput('text', 'filter[LT][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function text_between($name,$value = array(),$attributes=array()) {
        $html = parent::addLabel('From: ',array('class' => 'filter_label'));
        $html .= parent::addInput('text', 'filter[GTE][' . $name . ']', $value['from'], $attributes);
        $html .= "<br />";
        $html .= parent::addLabel('To: ',array('class' => 'filter_label'));
        $html .= parent::addInput('text', 'filter[LTE][' . $name . ']', $value['to'], $attributes);
        return $html;
    }
    
    public function date_equal($name,$value = '',$attributes=array()) {
        $attributes = array_merge($attributes,array('class' => 'filter_date'));
        $html = parent::addInput('text', 'filter[EQ][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function date_greater_equal($name,$value = '',$attributes=array()) {
        $attributes = array_merge($attributes,array('class' => 'filter_date'));
        $html = parent::addInput('text', 'filter[GTE][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function date_greater($name,$value = '',$attributes=array()) {
        $attributes = array_merge($attributes,array('class' => 'filter_date'));
        $html = parent::addInput('text', 'filter[GT][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function date_less_equal($name,$value = '',$attributes=array()) {
        $attributes = array_merge($attributes,array('class' => 'filter_date'));
        $html = parent::addInput('text', 'filter[LTE][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function date_less($name,$value = '',$attributes=array()) {
        $attributes = array_merge($attributes,array('class' => 'filter_date'));
        $html = parent::addInput('text', 'filter[LT][' . $name . ']', $value, $attributes);
        return $html;
    }
    
    public function date_between($name,$value = array(),$attributes=array()) {
        $attributes = array_merge($attributes,array('class' => 'filter_date'));
        $html = parent::addLabel('From: ',array('class' => 'filter_label'));
        $html .= parent::addInput('text', 'filter[GTE][' . $name . ']', $value['from'], $attributes);
        $html .= "<br />";
        $html .= parent::addLabel('To: ',array('class' => 'filter_label'));
        $html .= parent::addInput('text', 'filter[LTE][' . $name . ']', $value['to'], $attributes);
        return $html;
    }
    
    public function getFilterColumns($columns,$filter) {
        if(isset($filter['attributes'])) {
            $attributes = $filter['attributes'];
        } else {
            $attributes = array();
        }
        
        $arrColumns = array();
        foreach($columns as $column => $options) {
            if($options['function'] == 'select_equal') {
                $html = $this->select_equal($column, (isset($filter['EQ'][$column])?$filter['EQ'][$column]:''), array('style' => 'width: 100px;'), $options['option_list']);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'text_like_both') {
                $html = $this->text_like_both($column,(isset($filter['LKB'][$column])?$filter['LKB'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'text_like_front') {
                $html = $this->text_like_front($column,(isset($filter['LKF'][$column])?$filter['LKF'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'text_like_end') {
                $html = $this->text_like_end($column,(isset($filter['LKE'][$column])?$filter['LKE'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'text_equal') {
                $html = $this->text_equal($column,(isset($filter['EQ'][$column])?$filter['EQ'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'text_greater_equal') {
                $html = $this->text_greater_equal($column,(isset($filter['GTE'][$column])?$filter['GTE'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'text_greater') {
                $html = $this->text_greater($column,(isset($filter['GT'][$column])?$filter['GT'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'text_less_equal') {
                $html = $this->text_less_equal($column,(isset($filter['LTE'][$column])?$filter['LTE'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'text_less') {
                $html = $this->text_less($column,(isset($filter['LT'][$column])?$filter['LT'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'text_between') {
                $html = $this->text_between($column,array('from' => (isset($filter['GTE'][$column])?$filter['GTE'][$column]:''),'to' => (isset($filter['LTE'][$column])?$filter['LTE'][$column]:'')), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'date_equal') {
                $html = $this->date_equal($column,(isset($filter['EQ'][$column])?$filter['EQ'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'date_greater_equal') {
                $html = $this->date_greater_equal($column,(isset($filter['GTE'][$column])?$filter['GTE'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'date_greater') {
                $html = $this->date_greater($column,(isset($filter['GT'][$column])?$filter['GT'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'date_less_equal') {
                $html = $this->date_less_equal($column,(isset($filter['LTE'][$column])?$filter['LTE'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'date_less') {
                $html = $this->date_less($column,(isset($filter['LT'][$column])?$filter['LT'][$column]:''), $attributes);
                $arrColumns[$column] = $html;
            } elseif ($options['function'] == 'date_between') {
                $html = $this->date_between($column,array('from' => (isset($filter['GTE'][$column])?$filter['GTE'][$column]:''),'to' => (isset($filter['LTE'][$column])?$filter['LTE'][$column]:'')), $attributes);
                $arrColumns[$column] = $html;
            }
        }
        return $arrColumns;
    }
    
}

?>
