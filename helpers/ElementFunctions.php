<?php

/* 
* Example: get_element_id_by_value("TM 2001", "Subject")
*/
function get_element_by_value($search_string, $element_name, $collection_id = NULL){
	$db = get_db();
	$sql = "
	SELECT items.id 
	FROM {$db->Item} items 
	JOIN {$db->ElementText} element_texts 
	ON items.id = element_texts.record_id 
	JOIN {$db->Element} elements 
	ON element_texts.element_id = elements.id 
	JOIN {$db->ElementSet} element_sets 
	ON elements.element_set_id = element_sets.id 
	AND elements.name = '" . $element_name . "'
	AND element_texts.text = ? ";
	if ($collection_id) {
	    $sql .= "AND items.collection_id = '" . $collection_id . "'";
    }
	$itemIds = $db->fetchAll($sql, $search_string);
	if (count($itemIds) >= 1){
        $found_item = get_record_by_id('item', $itemIds[0]["id"]);
#        _log("get_element_by_value: " . $found_item->id , $priority = Zend_Log::DEBUG);
        return $found_item; //return $found_item->id; #return just the id
	}
	return null;
}

/* 
* Example: get_element_id_by_value("TM 2001", "Subject")
*/
function get_element_by_valueOLD($search_string, $element_name){
	$db = get_db();
	$sql = "
	SELECT items.id 
	FROM {$db->Item} items 
	JOIN {$db->ElementText} element_texts 
	ON items.id = element_texts.record_id 
	JOIN {$db->Element} elements 
	ON element_texts.element_id = elements.id 
	JOIN {$db->ElementSet} element_sets 
	ON elements.element_set_id = element_sets.id 
	AND elements.name = '" . $element_name . "' 
	AND element_texts.text = ?";
	$itemIds = $db->fetchAll($sql, $search_string);
	if (count($itemIds) >= 1){
        $found_item = get_record_by_id('item', $itemIds[0]["id"]);
        return $found_item->id;
#        return $found_item->id; #return just the id
	}
	return null;
}

function get_element_info_dynamic($search_string, $dublin_element_name, $show_elements_dublin = null, $show_elements_itemtype = null)
{
	if (!$show_elements_dublin || !$show_elements_itemtype){
		$show_elements = array("Title", "Creator", "Publisher");
#		$show_elements = array("Subgenre");
	}
	$db = get_db();
	$sql = "
	SELECT items.id 
	FROM {$db->Item} items 
	JOIN {$db->ElementText} element_texts 
	ON items.id = element_texts.record_id 
	JOIN {$db->Element} elements 
	ON element_texts.element_id = elements.id 
	JOIN {$db->ElementSet} element_sets 
	ON elements.element_set_id = element_sets.id 
	AND elements.name = '" . $element_name . "' 
	AND element_texts.text = ?";
	$itemIds = $db->fetchAll($sql, $search_string);
	print_r(get_class_methods($itemIds));
	if (count($itemIds) == 1){ //NOG EVEN MEE VERDER STOEIEN
		$temp_item = "";
		$found_item = get_record_by_id('item', $itemIds[0]["id"]);
		$temp_return = "<SPAN class='classic'>";
		foreach($show_elements_dublin as $show_element){
			$temp_return .= metadata($found_item, array('Dublin Core', $show_element)) . "<br>";
		}
		foreach($show_elements_itemtype as $show_element){
			$temp_return .= metadata($found_item, array('Item Type Metadata', $show_element)) . "<br>";
		}
		$temp_return .= "</SPAN>";
		return $temp_return;
	}
	return "no description";
}
?>