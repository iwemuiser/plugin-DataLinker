<?php

function type_info_retrieve_popup_jquery($args){
    $subject_element_number = 51; #Type
    $search_element = null;
    $return_element = null;
    $collection = 1;
    return jquery_double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}

function subgenre_info_retrieve_popup_jquery($args){
    $subject_element_number = 58; #Subgenre
    $search_element = null;
    $return_element = null;
    $collection = 1;
    $itemset = "Item Type Metadata";
    return jquery_double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}

function subject_info_retrieve_popup_jquery($args){
    $subject_element_number = 49; #Subject
    $search_element = "Identifier";
    $return_element = "Title";
    $collection = 1;
    return jquery_double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}

function creator_info_retrieve_popup_jquery($args){
    $subject_element_number = 39; #Creator
    $search_element = "Title";
    $return_element = "Title";
    $collection = 4;
    return jquery_double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}


function language_info_retrieve_popup_jquery($args){
    $subject_element_number = 44; #Language
    $search_element = null;
    $return_element = null;
    $collection = 1;
    return jquery_double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}

function collector_info_retrieve_popup_jquery($args){
    $subject_element_number = 60; #Collector
    $search_element = null;
    $return_element = null;
    $collection = 1;
    $itemset = "Item Type Metadata";
    return jquery_double_field_info($subject_element_number, $search_element, $return_element, $collection, $args, $itemset);
}

function present_dates_as_language($args){
    return $args;
}

function jquery_double_field_info($subject_element_number, $search_element, $return_element, $collection, $args, $itemset = 'Dublin Core'){
    if (empty($args)){ return $args; }
    $type_information = $args;
    if (get_element_by_value($args, $search_element) && $search_element){
        $element_texts = get_element_by_value($args, $search_element)->getElementTexts($itemset, $return_element);
        $type_information = $args . " - " . $element_texts[0]["text"];
    }
    $pasted_args = str_replace(array(" ", "\r", "*", ")", "(", ",", "-", ".", ":"), "", $args);
    $html = '
        <p class="toggler" id="toggler-'.$pasted_args.'">
            <span class="expandSlider">' . $type_information . ' &nbsp&nbsp&nbsp <img src= "' . url("themes/verhalenbank/images/down.gif").'"></span>
            <span class="collapseSlider">' . $type_information . ' &nbsp&nbsp&nbsp <img src= "'   . url("themes/verhalenbank/images/up.gif").'"></span>
        </p>
        <div class="slider" id="'.$pasted_args.'">'
            . info_search_link($subject_element_number, $args, $collection) . '<br/>'
            . info_item_link($search_element, $args) . '
        </div>';
    return $html;
}

function info_item_link($element_name, $search_term){
#    $element = get_db()->getTable("Element")->find($element_number);
#    $element_name = $element->name;
    if (get_element_by_value($search_term, $element_name)){
        $url = record_url(get_element_by_value($search_term, $element_name), 'show');
        return '<a href='.$url.'>Alle informatie over ' . $search_term . '</a>';
    }
    return "";
}

function scroll_to_full_text($args){
    return $args . "<br><b><a id='text-scroll' href='#volksverhaal-item-type-metadata-text'>" . __("Bekijk volledige tekst") . "</a></b>";
}


/*
*   returns hidden HTML with links to an item and to a list of items containing this Id
*   @arguments:
*   
* @param int $element_number     The element number that should be searched
* @param int $search_term       The term that should be searched
* @return string                Link to a search
*/
function info_search_link($element_number, $search_term, $collection = 1){
    $element = get_db()->getTable("Element")->find($element_number);
    $element_name = $element->name;
    $taletype_search_url = url(array('module'=>'items','controller'=>'browse'), 'default', 
                            array("search" => "",
                                "submit_search" => "Zoeken",
                                "collection" => $collection,
                                "advanced[0][element_id]" => "$element_number",
                                "advanced[0][type]" => "is exactly",
                                "advanced[0][terms]" => "$search_term"));
    return "<a href='".$taletype_search_url."'>Alle verhalen met " . __($element_name) . ": " . $search_term . "</a>";
}

function text_copyright_hide($args){
	if ($user = current_user()){
		return $args;
	}
	if ($args){
		if (metadata(get_current_record('item'), array('Dublin Core', 'Rights')) == "nee"){
			return "<b textcolor = 'red'>Auteursrecht:</b> 
			De tekst bevat auteursrechtelijk beschermde informatie. 
			De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
			<br>
			This text contains copyrighted information.";
		}
		else{
			return $args;
		}
	}
	else{
		return false;
	}
}


function text_extreme_hide($args){
	if ($user = current_user()){
		return $args;
	}
	if ($args){
		if (metadata(get_current_record('item'), array('Item Type Metadata', 'Extreme')) == "ja"){
			return "<b textcolor = 'red'>Extreme:</b> 
			Dit record bevat extreme elementen van enigerlei aard (racisme, sexisme, schuttingtaal, godslastering, expliciete naaktheid, majesteitsschennis). 
			De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
			<br>
			This text contains language that can be perceived as extreme.";
		}
		else{
			return $args;
		}
	}
	else{
		return false;
	}
}
?>