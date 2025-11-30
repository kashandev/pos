<?php

class ControllerSetupDocument extends HController
{

    protected function getAlias()
    {
        return 'setup/document';
    }

    protected function getPrimaryKey()
    {
        return 'document_id';
    }

    protected function init()
    {
        $this->model[$this->getAlias()] = $this->load->model('common/document');
        $this->data['lang'] = $this->load->language('setup/document');
        $this->document->setTitle($this->data['lang']['heading_title']);
        $this->data['token'] = $this->session->data['token'];
    }

    public function index()
    {
        $this->redirect($this->url->link($this->getAlias() . '/update', 'token=' . $this->session->data['token'] . '&document_id=' . $this->session->data['document_id'], 'SSL'));
    }

    protected function getForm()
    {
        parent::getForm();

        $this->data['partner_types'] = $this->session->data['partner_types'];
        $this->model['partner'] = $this->load->model('common/partner');
        $this->data['partners'] = $this->model['partner']->getRows(array(), array('name'));

        $this->data['document_types'] = $this->model[$this->getAlias()]->getDistinctRows(array('document_type_id','document_name'),array(), array('document_name'));
        //d($this->data['partner_types'], true);

        $this->data['href_get_documents'] = $this->url->link($this->getAlias() . '/getDocuments', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['href_unpost_document'] = $this->url->link($this->getAlias() . '/unPostDocument', 'token=' . $this->session->data['token'], 'SSL');
        $this->response->setOutput($this->render());
    }

    public function getDocuments() {
        $post = $this->request->post;
        $filter = array();
        $filter[] = "`company_id` >= '".$this->session->data['company_id']."'";
        $filter[] = "`company_branch_id` >= '".$this->session->data['company_branch_id']."'";
        $filter[] = "`fiscal_year_id` >= '".$this->session->data['fiscal_year_id']."'";
        $filter[] = "`is_post` >= 1";
        if(isset($post['document_from_date']) && $post['document_from_date']) {
            $filter[] = "`document_date` >= '".MySqlDate($post['document_from_date'])."'";
        }
        if(isset($post['document_to_date']) && $post['document_to_date']) {
            $filter[] = "`document_date` <= '".MySqlDate($post['document_to_date'])."'";
        }
        if(isset($post['post_from_date']) && $post['post_from_date']) {
            $filter[] = "`post_date` >= '".MySqlDate($post['post_from_date'])."'";
        }
        if(isset($post['post_to_date']) && $post['post_to_date']) {
            $filter[] = "`post_date` <= '".MySqlDate($post['post_to_date'])."'";
        }
        if(isset($post['document_type_id']) && $post['document_type_id']) {
            $filter[] = "`document_type_id` = '".$post['document_type_id']."'";
        }
        if(isset($post['partner_type_id']) && $post['partner_type_id']) {
            $filter[] = "`partner_type_id` = '".$post['partner_type_id']."'";
        }
        if(isset($post['partner_id']) && $post['partner_id']) {
            $filter[] = "`partner_id` = '".$post['partner_id']."'";
        }
        if($filter) {
            $where = implode(' AND ', $filter);
        } else {
            $where = array();
        }
        $this->model['document'] = $this->load->model('common/document');
        $rows = $this->model['document']->getRows($where);

        $this->model['user'] = $this->load->model('user/user');
        $arrUsers = $this->model['user']->getArrays('user_id','user_name');
        $html = '';
        foreach($rows as $row) {
            $href = $this->url->link($row['route'] . '/update', 'token=' . $this->session->data['token'] . '&'.$row['primary_key_field'].'=' . $row['primary_key_value'], 'SSL');
            $html .= '<tr>'.PHP_EOL;
            $html .= '<td>'.PHP_EOL;
            if($row['is_post']) {
                $html.= '<button type="button" class="btn btn-primary btn-sm" data-document_id="'.$row['document_id'].'" onclick="unPost(this);">UnPost</button>';
            }
            $html .= '</td>'.PHP_EOL;
            $html .= '<td>'.$row['document_name'].'</td>'.PHP_EOL;
            $html .= '<td>'.stdDate($row['document_date']).'</td>'.PHP_EOL;
            $html .= '<td>'.PHP_EOL;
            $html.= '<a href="'.$href.'" target="_blank" >' . $row['document_identity'] . '</a>';
            $html .= '</td>'.PHP_EOL;
            $html .= '<td>'.$row['partner_type'].'</td>'.PHP_EOL;
            $html .= '<td>'.$row['partner_name'].'</td>'.PHP_EOL;
            if($row['post_date']=='') {
                $html .= '<td>&nbsp;</td>'.PHP_EOL;
            } else {
                $html .= '<td>'.stdDateTime($row['post_date']).'</td>'.PHP_EOL;
            }
            $html .= '<td>'.$arrUsers[$row['post_by_id']].'</td>'.PHP_EOL;
            $html .= '<td>'.stdDateTime($row['created_at']).'</td>'.PHP_EOL;
            $html .= '</tr>'.PHP_EOL;
        }

        echo json_encode(array(
            'success' => true,
            'where' => $where,
            'html' => $html
        ));
    }

    public function unPostDocument() {
        $post = $this->request->post;
        $document_id = $post['document_id'];
        $this->model['document'] = $this->load->model('common/document');
        $document = $this->model['document']->getRow(array('document_id' => $document_id));
        $this->db->beginTransaction();
        $data = array(
            'is_post' => NULL,
            'post_date' => NULL,
            'post_by_id' => NULL
        );
        $this->model['document']->edit($this->getAlias(), $document_id, $data);

        $this->model['table'] = $this->load->model($document['route']);
        $this->model['table']->edit($this->getAlias(), $document_id, $data);
        $this->db->commit();
        $json = array(
            'success' => true,
            'post' => $post,
            'document' => $document,
        );

        echo json_encode($json);
    }

}

?>