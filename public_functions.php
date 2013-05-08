<?php

function identifier_info_retrieve_popup_jquery($args){
    $subject_element_number = 49; //subject zoeken
    $search_element = null;
    $return_element = null;
    $collection = 1;
    return double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}

function type_info_retrieve_popup_jquery($args){
    $subject_element_number = 51; #Type
    $search_element = null;
    $return_element = null;
    $collection = 1;
    return double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}

function subgenre_info_retrieve_popup_jquery($args){
    $subject_element_number = 58; #Subgenre
    $search_element = null;
    $return_element = null;
    $collection = 1;
    $return_itemset = "Item Type Metadata";
    return double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}

function subject_info_retrieve_popup_jquery($args){
    $subject_element_number = 49; #Subject
    $search_element = "Identifier";
    $return_element = "Title";
    $collection = 1;
    return double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}

function creator_info_retrieve_popup_jquery($args){
    $subject_element_number = 39; #Creator
#    $search_element = "Title";
#    $return_element = "Title";
    $search_element = null;
    $return_element = null;
    $collection = 1;
    return double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}


function language_info_retrieve_popup_jquery($args){
    $subject_element_number = 44; #Language
    $search_element = null;
    $return_element = null;
    $collection = 1;
    return double_field_info($subject_element_number, $search_element, $return_element, $collection, $args);
}

function collector_info_retrieve_popup_jquery($args){
    $subject_element_number = 60; #Collector
    $search_element = null;
    $return_element = null;
    $collection = 1;
    $return_itemset = "Item Type Metadata";
    return double_field_info($subject_element_number, $search_element, $return_element, $collection, $args, $return_itemset);
}

function present_dates_as_language($args){
    return $args;
}

function double_field_info($subject_element_number, $search_element = null, $return_element = null, $collection = null, $original_value = null, $return_itemset = 'Dublin Core'){
#    print "-" . $subject_element_number . " - ". $search_element . " - ". $return_element . " - ". $collection . " - ". $original_value . " - ". $return_itemset;
    $html = "";
    $links = array();
    $supplemented_value = $original_value;
    $additional_information = null;
    if (!empty($original_value) && $search_element && $return_element){
        $additional = get_element_by_value($original_value, $search_element);
        if ($additional) { 
            $additional_information_pre = $additional->getElementTexts($return_itemset, $return_element);
            $additional_information = $additional_information_pre[0]["text"];
        }
    }
    $links[] = info_search_link($subject_element_number, $original_value, $collection);
    $links[] = info_item_link($search_element, $original_value, 3, "verhaaltype");         // check if the link to the item can be found
    $links[] = info_item_link("Subject", $original_value, 2, "in lexicon");         //check if the value can be found in subcollection Lexicon
    $links[] = info_item_link("Subject", $original_value, 6, "in Perrault");        //check if the value can be found in subcollection Lexicon
    $links[] = info_item_link("Subject", $original_value, 7, "in Grimm");           //check if the value can be found in subcollection Lexicon
    if (is_admin_theme()) {
        $html = browse_link_in_table($original_value, $additional_information, $links); //the additional information is put in a table format
    }
    else{
        $html = browse_link_in_toggler($original_value, $additional_information, $links);   // the additional information is put in a jquery toggler
#        $html = browse_link_in_menu($original_value, $additional_information, $links);   // a menu with hover
    }
    return $html;
}

function browse_link_in_menu($original_value, $additional_information, $links){
    $html = $original_value;
    $supplemented_value = $original_value . ($additional_information ? " - " . $additional_information : "");
    if ($supplemented_value){
        $pasted_args = str_replace(array(" ", "\r", "*", ")", "(", ",", "-", ".", ":"), "", $original_value); //for unique id name
        $html = '
        <div id="button">
            <ul class="hover">
               <li class="hoverli">
                   <p>' . $supplemented_value . '</p>
                    <ul class="file_menu">';
        foreach ($links as $link){
            if ($link){$html .= '<li>'.$link.'</li>';}
        }
        $html .= '  </ul>
                <li>
            </ul>
        </div>';
    }
    return $html;
}


function browse_link_in_toggler($original_value, $additional_information, $links){
    $html = $original_value;
    $supplemented_value = $original_value . ($additional_information ? " - " . $additional_information : "");
    if ($supplemented_value){
        $pasted_args = str_replace(array(" ", "\r", "*", ")", "(", ",", "-", ".", ":"), "", $original_value);
        $html = '
            <p class="toggler" id="toggler-' . $pasted_args . '">
                <span class="expandSlider">' . $supplemented_value . ' &nbsp&nbsp&nbsp <img src= "' . url("themes/verhalenbank/images/down.gif").'"></span>
                <span class="collapseSlider">' . $supplemented_value . ' &nbsp&nbsp&nbsp <img src= "'   . url("themes/verhalenbank/images/up.gif").'"></span>
            </p>
            <div class="slider" id="'.$pasted_args.'">';
        foreach ($links as $link){ $html .= $link;}
        $html .= '</div>';
    }
    return $html;
}

function browse_link_in_table($original_value, $additional_information, $links){
    $html = $original_value . '<table>';
    if ($additional_information){
        $html .= 
        '    <tr>
                <th>' . 
                    $additional_information . '<br />
                </th>
            </tr>';
    }
        $html .= '<tr>
            <td>';
    foreach ($links as $link){ $html .= $link;}
    $html .= '</td></tr>';
        $html .= '</table>';
    return $html;
}


function info_item_link($element_name, $search_term, $collection_id = NULL, $ga_naar_text = ""){
    if (get_element_by_value($search_term, $element_name, $collection_id)){
        $url = record_url(get_element_by_value($search_term, $element_name, $collection_id), 'show');
        return '<a href='.$url.'>' . $search_term . ' '  . $ga_naar_text . '</a><br>';
    }
    return "";
}

function scroll_to_full_text($args){
    $itemtype = "volksverhaal";
    return $args . "<br><b><a id='text-scroll' href='#$itemtype-item-type-metadata-text'>" . __("Bekijk volledige tekst") . "</a></b>";
}


/*
*   returns links to an items in subcollections containing this Id
*   @arguments:
*   
* @param int $element_number     The element number to be searched in
* @param int $search_term       The term that should be searched for
* @return string                Links to items in subcollections
*/
function info_subcollection_items($element_number, $search_term, $collections = array(2,6,7)){
    $element = get_db()->getTable("Element")->find($element_number);
    $element_name = $element->name;
    $links = "";
    foreach($collections as $collection){
        $taletype_search_url = url(array('module'=>'items','controller'=>'browse'), 'default', 
                                array("search" => "",
                                    "submit_search" => "Zoeken",
                                    "collection" => $collection,
                                    "advanced[0][element_id]" => "$element_number",
                                    "advanced[0][type]" => "is exactly",
                                    "advanced[0][terms]" => "$search_term"));
        $links .= "<a href='".$taletype_search_url."'> " . __($element_name) . get_record_by_id('Collection', $collection)->name . ": " . $search_term . "</a><br>";
    }
    return $links;
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
    return "<a href='".$taletype_search_url."'>Alles met " . __($element_name) . ": " . $search_term . "</a><br>";
}

function text_copyright_hide($args){
	if ($user = current_user()){
		return $args;
	}
	if ($args){
		if (metadata(get_current_record('item'), array('Dublin Core', 'Rights')) == "nee"){
            return get_option('textcopyrightwarning');
			/*"<p textcolor = 'red'><b>Auteursrecht:</b></p>
			De tekst bevat auteursrechtelijk beschermde informatie. 
			De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
			<br>
			This text contains copyrighted information.";*/
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
			return get_option('textextremewarning');
/*			"<p textcolor = 'red'><b>Extreme:</b></p> 
			Dit record bevat extreme elementen van enigerlei aard (racisme, sexisme, schuttingtaal, godslastering, expliciete naaktheid, majesteitsschennis). 
			De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut.
			<br>
			This text contains language that can be perceived as extreme.";*/
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