<?php
/*------------------------------------------------------------------------
# SEO Boss pro
# ------------------------------------------------------------------------
# author    JoomBoss
# copyright Copyright (C) 2012 Joomboss.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.joomboss.com
# Technical Support:  Forum - http://joomboss.com/forum
-------------------------------------------------------------------------*/
// no direct access
defined('_JEXEC') or die('Restricted access');
require_once "MetatagsContainer.php";
class VM2_ProductMetatagsContainer extends MetatagsContainer{
    public $code=4;
    public function getMetatags($lim0, $lim, $filter=null){
        $db = JFactory::getDBO();
        $language = $this->getLanguage();
        $sql = "SELECT SQL_CALC_FOUND_ROWS
        c.virtuemart_product_id as id,
        c.product_name AS title,
        c.metakey AS metakey,
        if (ISNULL(c.metadesc) OR c.metadesc='',c.product_s_desc,c.metadesc) as metadesc,
        c.customtitle as metatitle ,
        m.title_tag as title_tag
         FROM
        #__virtuemart_products_$language c
        LEFT JOIN
		#__seoboss_metadata m ON m.item_id=c.virtuemart_product_id and m.item_type={$this->code}
        WHERE 1 ";

        $search = JRequest::getVar("filter_search", "");
        $category_id = JRequest::getVar("filter_category_id", "0");
        $com_vm_filter_show_empty_keywords =
            JRequest::getVar("com_vm_filter_show_empty_keywords", "-1");
        $com_vm_filter_show_empty_descriptions =
            JRequest::getVar("com_vm_filter_show_empty_descriptions", "-1");

        if ($search != ""){
            if (is_numeric($search)){
                $sql .= " AND c.virtuemart_product_id=".$db->quote($search);
            }else{
                $sql .= " AND c.product_name LIKE ".$db->quote('%'.$search.'%');
            }
        }
        if ($category_id > 0){
            $sql .= " AND EXISTS (SELECT 1 FROM #__virtuemart_product_categories WHERE #__virtuemart_product_categories.virtuemart_category_id=".$db->quote($category_id)." AND #__virtuemart_product_categories.virtuemart_product_id=c.virtuemart_product_id)";

        }

        if ($com_vm_filter_show_empty_keywords != "-1"){
            $sql .= " AND (ISNULL(metakey) OR metakey='') ";
        }
        if ($com_vm_filter_show_empty_descriptions != "-1"){
            $sql .= "AND (ISNULL(metadesc) OR metadesc='') ";
        }
        //Sorting
        $order = JRequest::getCmd("filter_order", "title");
        $order_dir = JRequest::getCmd("filter_order_Dir", "ASC");
        switch($order){
            case "meta_title":
                $sql .= " ORDER BY metatitle ";
                break;
            case "meta_key":
                $sql .= " ORDER BY metakey ";
                break;
            case "meta_desc":
                $sql .= " ORDER BY metadesc ";
                break;
            case "title_tag":
                $sql .= " ORDER BY title_tag ";
                break;
            default:
                $sql .= " ORDER BY title ";
                break;

        }
        if ($order_dir == "asc"){
            $sql .= " ASC";
        }else{
            $sql .= " DESC";
        }

        $db->setQuery($sql, $lim0, $lim);
        $rows = $db->loadObjectList();
        if ($db->getErrorNum()) {
            echo $db->stderr();
            return false;
        }
        for($i = 0 ; $i < count($rows);$i++){
            $rows[$i]->edit_url = "index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id={$rows[$i]->id}";
        }
        return $rows;
    }

    public function mustReplaceMetaTitle(){
      return false;
    }
    public function mustReplaceMeteaKeywords(){
      return false;
    }
    public function mustReplaceMetaDescription(){
      return false;
    }

    public function copyKeywordsToTitle($ids){
        $language = $this->getLanguage();
        $db = JFactory::getDBO();
        foreach($ids as $key=>$value){
            if (!is_numeric($value)){
                unset($ids[$key]);
            }
        }
        if (count($ids) > 0){
            $sql = "UPDATE #__virtuemart_products_$language SET customtitle=metakey WHERE virtuemart_product_id IN (".
                               implode(",", $ids). ")";
            $db->setQuery($sql);
            $db->query();
        }
    }

    public function copyTitleToKeywords($ids){
       $language = $this->getLanguage();
       $db = JFactory::getDBO();
        foreach($ids as $key=>$value){
            if (!is_numeric($value)){
                unset($ids[$key]);
            }
        }
        if (count($ids) > 0){
            $sql = "UPDATE #__virtuemart_products_$language SET metakey=customtitle WHERE virtuemart_product_id IN (".
                               implode(",", $ids). ")";
            $db->setQuery($sql);
            $db->query();

            //save keywords
            $sql = "SELECT virtuemart_product_id as id , metakey FROM #__virtuemart_products_$language WHERE virtuemart_product_id IN (".
            implode(",", $ids). ")";
            $db->setQuery($sql);
            $items = $db->loadObjectList();
            foreach($items as $item){
                $this->saveKeywords($item->metakey, $item->id);
            }
        }
    }

    public function copyItemTitleToTitle($ids){
        $db = JFactory::getDBO();
        foreach($ids as $key=>$value){
            if (!is_numeric($value)){
                unset($ids[$key]);
            }
        }
    if (count($ids) > 0){
            $language = $this->getLanguage();
            $sql = "UPDATE #__virtuemart_products_$language SET customtitle=product_name WHERE virtuemart_product_id IN (".
                               implode(",", $ids). ")";
            $db->setQuery($sql);
            $db->query();
        }
    }

    public function copyItemTitleToKeywords($ids){
        $db = JFactory::getDBO();
        $language = $this->getLanguage();
        foreach($ids as $key=>$value){
            if (!is_numeric($value)){
                unset($ids[$key]);
            }
        }
        if (count($ids) > 0){
            $sql = "UPDATE #__virtuemart_products_$language SET metakey=product_name WHERE virtuemart_product_id IN (".
                               implode(",", $ids). ")";
            $db->setQuery($sql);
            $db->query();

            //save keywords
            $sql = "SELECT virtuemart_product_id as id , metakey FROM #__virtuemart_products_$language WHERE virtuemart_product_id IN (".
                               implode(",", $ids). ")";
            $db->setQuery($sql);
            $items = $db->loadObjectList();
            foreach($items as $item){
                $this->saveKeywords($item->metakey, $item->id);
            }
        }
    }

    public function GenerateDescriptions($ids){
      $max_description_length = 500;
      $model = JBModel::getInstance("options", "SeobossModel");
      $params = $model->getOptions();
      $max_description_length =
        $params->max_description_length?
         $params->max_description_length:
         $max_description_length;
        $db = JFactory::getDBO();
        $language = $this->getLanguage();
        foreach($ids as $key=>$value){
            if (!is_numeric($value)){
                unset($ids[$key]);
            }
        }

        $sql = "SELECT virtuemart_product_id, product_s_desc
                FROM  #__virtuemart_products_$language
                WHERE virtuemart_product_id IN (".implode(",", $ids).")";
        $db->setQuery($sql);
        $items = $db->loadObjectList();

        foreach($items as $item){
          if ($item->product_s_desc != ''){
            $introtext = strip_tags($item->product_s_desc);
            if (strlen($introtext) > $max_description_length){
              $introtext = substr($introtext, 0, $max_description_length);
            }
            $sql = "INSERT INTO #__seoboss_metadata (item_id,
            item_type, title, description)
            VALUES (
            ".$db->quote($item->virtuemart_product_id).",
            {$this->getTypeId()},

            '',
            ".$db->quote($introtext)."
           ) ON DUPLICATE KEY UPDATE description=".$db->quote($introtext);

            $db->setQuery($sql);
            $db->query();

            $sql = "UPDATE #__virtuemart_products_$language
                    SET metadesc=".$db->quote($introtext)."
                    WHERE virtuemart_product_id=".$db->quote($item->virtuemart_product_id);

            $db->setQuery($sql);
            $db->query();
          }
        }
    }

    public function getPages($lim0, $lim, $filter=null){
        $db = JFactory::getDBO();
        $language = $this->getLanguage();
        $sql = "SELECT SQL_CALC_FOUND_ROWS
        	c.virtuemart_product_id AS id,
        	c.product_name AS title,
        	c.metakey AS metakey,
        	c.product_desc AS content
         FROM
        #__virtuemart_products_$language c WHERE 1
        ";

        $search = JRequest::getVar("filter_search", "");
        $category_id = JRequest::getVar("filter_category_id", "0");
        $com_vm_filter_show_empty_keywords = JRequest::getVar("com_vm_filter_show_empty_keywords", "-1");
        $com_vm_filter_show_empty_descriptions = JRequest::getVar("com_vm_filter_show_empty_descriptions", "-1");

        if ($search != ""){
            if (is_numeric($search)){
                $sql .= " AND c.virtuemart_product_id=".$db->quote($search);
            }else{
                $sql .= " AND c.product_name LIKE ".$db->quote('%'.$search.'%');
            }
        }
        if ($category_id > 0){
            $sql .= " AND EXISTS (SELECT 1 FROM #__virtuemart_product_categories WHERE #__virtuemart_product_categories.virtuemart_category_id=".$db->quote($category_id)." AND #__virtuemart_product_categories.virtuemart_product_id=c.virtuemart_product_id)";

        }

        if ($com_vm_filter_show_empty_keywords != "-1"){
            $sql .= " AND (ISNULL(metakey) OR metakey='') ";
        }
        if ($com_vm_filter_show_empty_descriptions != "-1"){
            $sql .= "AND (ISNULL(metadesc) OR metadesc='') ";
        }

        $db->setQuery($sql, $lim0, $lim);

        $rows = $db->loadObjectList();
        if ($db->getErrorNum()) {
            echo $db->stderr();
            return false;
        }
        // Get outgoing links and keywords density
        for($i = 0 ; $i < count($rows);$i++){
            if ($rows[$i]->metakey){
                $rows[$i]->metakey = explode(",", $rows[$i]->metakey);
            }else{
                $rows[$i]->metakey = array("");
            }
                $rows[$i]->edit_url = "index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id={$rows[$i]->id}";
        }
        return $rows;
    }
    public function saveMetatags($ids, $metatitles, $metadescriptions, $metakeys, $title_tags=null){
        $db = JFactory::getDBO();
        $language = $this->getLanguage();
        for($i = 0 ;$i < count($ids); $i++){
            $sql = "UPDATE #__virtuemart_products_$language
             SET customtitle=".$db->quote($metatitles[$i]).",
             metadesc=".$db->quote($metadescriptions[$i]).",
             metakey=".$db->quote($metakeys[$i])."
             WHERE virtuemart_product_id=".$db->quote($ids[$i]);
            $db->setQuery($sql);
            $db->query();
            $this->saveKeywords($metakeys[$i], $ids[$i]);

            $sql = "INSERT INTO #__seoboss_metadata (item_id,
            item_type, title, description, title_tag)
            VALUES (
            ".$db->quote($ids[$i]).",
            {$this->code},
            ".$db->quote($metatitles[$i]).",
            ".$db->quote($metadescriptions[$i]).",
            ".$db->quote($title_tags!=null?$title_tags[$i]:'')."
           ) ON DUPLICATE KEY UPDATE title=".$db->quote($metatitles[$i])." , description=".$db->quote($metadescriptions[$i]).
            ", title_tag=".$db->quote($title_tags!=null?$title_tags[$i]:'');

            $db->setQuery($sql);
            $db->query();
        }

    }
    public function saveKeywords($keys, $id){
        parent::saveKeywords($keys, $id,$this->code);
    }

    public function getItemData($id){
        $db = JFactory::getDBO();
        $language = $this->getLanguage();
        $sql = "SELECT c.virtuemart_product_id as id,
        c.product_name as title,
        c.metakey AS metakeywords,
        c.metadesc as metadescription,
        c.customtitle as metatitle
         FROM
        #__virtuemart_products_$language c
        WHERE c.virtuemart_product_id=".$db->quote($id);
        $db->setQuery($sql);
        $data = $db->loadAssoc();
        $parentData = parent::getItemData($id);
        $data["title_tag"] = $parentData["title_tag"];
        return $data;
    }


    public function setMetadata($id, $data){
        $keywords = $data["metakeywords"];
        $title = isset($data["title"])?$data["title"]:"";
        $metatitle = $data["metatitle"];
        $metadescription = $data["metadescription"];
        $language = $this->getLanguage();
        $db = JFactory::getDBO();
        //Save metatitles and metadata
        $sql = "UPDATE #__virtuemart_products_$language
        		SET ".
        		($title?"product_name= ".$db->quote($title).",":"")."
        		customtitle=".$db->quote($metatitle).",
        		metadesc=".$db->quote($metadescription).",
        		metakey=".$db->quote($keywords)."
        		WHERE virtuemart_product_id=".$db->quote($id);
        $db->setQuery($sql);
        $db->query();
        parent::setMetadata($id, $data);
    }

    public function getMetadataByRequest($query){
      $params = array();
      parse_str($query, $params);
      $metadata = null;
      if (isset($params["virtuemart_product_id"])){
        $metadata = $this->getMetadata($params["virtuemart_product_id"]);
      }
      return $metadata;
    }

    public function setMetadataByRequest($query, $data){
      $params = array();
      parse_str($query, $params);
      if (isset($params["virtuemart_product_id"]) && $params["virtuemart_product_id"]){
        $this->setMetadata($params["virtuemart_product_id"], $data);
      }
    }

    function getFilter(){
        $language = $this->getLanguage();
        $search = JRequest::getVar("filter_search", "");
        $category_id = JRequest::getVar("filter_category_id", "");

        $com_vm_filter_show_empty_keywords = JRequest::getVar("com_vm_filter_show_empty_keywords", "-1");
        $com_vm_filter_show_empty_descriptions = JRequest::getVar("com_vm_filter_show_empty_descriptions", "-1");

                $result =  'Filter:
        <input type="text" name="filter_search" id="search" value="'.$search.'" class="text_area" onchange="document.adminForm.submit();" title="Filter by Title or enter an Product ID"/>
        <button onclick="this.form.submit();">Go</button>
        <button onclick="document.getElementById(\'search\').value=\'\';this.form.getElementById(\'filter_sectionid\').value=\'-1\';this.form.getElementById(\'catid\').value=\'0\';this.form.getElementById(\'filter_authorid\').value=\'0\';this.form.getElementById(\'filter_state\').value=\'\';this.form.submit();">Reset</button>

        &nbsp;&nbsp;&nbsp;';

        $result .=  "<select name=\"filter_category_id\" onchange=\"document.adminForm.submit();\">".
        "<option value=\"\">Select Category</option>";

        $db = JFactory::getDBO();
        $db->setQuery("SELECT virtuemart_category_id as id , category_name as name FROM #__virtuemart_categories_$language ORDER BY category_name");
        $categories = $db->loadObjectList();
        foreach($categories as $category){
            $result .= "<option value=\"{$category->id}\" ".($category->id==$category_id?"selected=\"true\"":"").">{$category->name}</option>";
        }
        $result .= "</select>";


        $result .= '<br/>
        <label>Show only Items with empty keywords</label>
        <input type="checkbox" onchange="document.adminForm.submit();" name="com_vm_filter_show_empty_keywords" '.($com_vm_filter_show_empty_keywords!="-1"?'checked="yes" ':'').'/>
        <label>Show only Items with empty descriptions</label>
        <input type="checkbox" onchange="document.adminForm.submit();" name="com_vm_filter_show_empty_descriptions" '.($com_vm_filter_show_empty_descriptions!="-1"?'checked="yes" ':'').'/>                ';
        return $result;
    }

    private function getLanguage(){
        $language="en_gb";
        $vmHelperPath = dirname(__FILE__)."/../../com_virtuemart/helpers/config.php";
        if (is_file($vmHelperPath)){
            require_once($vmHelperPath);
            $config = VmConfig::loadConfig();
            $language = $config->lang;
        }
        return $language;
    }

    public function getTypeId(){
      return $this->code;
    }

    public function isAvailable(){
      return file_exists(dirname(__FILE__)."/../../com_virtuemart/models/virtuemart.php");
    }


}