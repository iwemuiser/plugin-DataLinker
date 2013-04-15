<?php
if (!defined('DATALINKER_PLUGIN_DIR')) {
    define('DATALINKER_PLUGIN_DIR', dirname(__FILE__));
}
if (!defined('DATALINKER_IMAGE_DIR')) {
    define('DATALINKER_IMAGE_DIR', dirname(__FILE__) . "/views/shared/images");
}

require_once DATALINKER_PLUGIN_DIR . '/helpers/ElementFunctions.php';
require_once DATALINKER_PLUGIN_DIR . '/admin_functions.php';
require_once DATALINKER_PLUGIN_DIR . '/public_functions.php';


class DataLinkerPlugin extends Omeka_Plugin_AbstractPlugin
{
	protected $_hooks = array('public_head',
#                                'public_items_show_top',
                                'public_items_show_sidebar_top',
                                'public_items_show_sidebar_ultimate_top',
                                'admin_head',
#	                            'admin_items_show',
                                'admin_items_show_sidebar',
                                'initialize');
	
	protected $_filters = array('display_elements',
	                            'file_markup',
	                            'item_citation',
#                                'admin_navigation_main',
#                                'public_navigation_main'
                                );

    public $_metadata_public_hide = array("Dublin Core" => array("Contributor", "Rights", "Creator"), #CREATOR TEMPORARY
                                            "Item Type Metadata" => array("Extreme", "Kloeke Georeference", "Entry date"));
    
    public $_metadata_to_the_right = array("Dublin Core" => array("Creator", "Contributor", "Type", "Language"),
                                            "Item Type Metadata" => array("Collector"));

    /**
     * Initialize the plugin.
     */
    public function hookInitialize(){
    }

	/**
	* admin_items_show_sidebar
	*   
	**/
    public function hookAdminItemsShowSidebar($args){
        contributor_information_tab_admin($args);
	}

    public function filterItemCitation($citation, $args) {
        $citation = "None";
        return $citation;
    }

    public function filterDisplayElements($elementSets) {
        //here we take out the elements that will be shown on the div on the metadata div
        foreach($elementSets as $setName=>$elements) {
            foreach($elements as $element) {
                foreach($this->_metadata_to_the_right as $sn=>$el){
                    if(in_array($element->name, $el)){
                        unset($elementSets[$setName][$element->name]);
                    }
                }
            }
        }
        if ($user = current_user()){ #don't filter this stuff out when logged in
            return $elementSets;
        }
        //here we filter the elements that should not be seen by the public
        foreach($elementSets as $setName=>$elements) {
            foreach($elements as $element) {
                foreach($this->_metadata_public_hide as $sn=>$el){ #omitting the element set names
                    if(in_array($element->name, $el)){
                        unset($elementSets[$setName][$element->name]);
                    }
                }
            }
        }
        return $elementSets;
    }

    public function hookPublicItemsShowSidebarTop($args){
        $_metadata_fields_public_hide = array_merge($this->_metadata_public_hide["Dublin Core"], $this->_metadata_public_hide["Item Type Metadata"]);
        $item = $args['item'];
        print '<div id="item-metadata" class="element">';
        print '<h2>Metadata</h2>';
        foreach($this->_metadata_to_the_right as $setName=>$elements) {
            foreach($elements as $element) {
                if (!in_array($element, $_metadata_fields_public_hide) || $user = current_user()){ //to check if it is allowed to show the item publicly AND if a user is logged in
                    if (strlen(metadata('item', array($setName, $element))) > 0){ // don't show when empty
                        print '<div id="" class="element">';
                        print "<h3>" . __($element) . " </h3>";
                        print '<div class="element-text">' . metadata('item', array($setName, $element)) . "</div>";
                        print '</div>';
                    }
                }
            }
        }
        print "</div>";
    }

    public function hookPublicItemsShowSidebarUltimateTop($args){
        print "<ul class='slide-toggle'>";
        print '<li class="up" id="slidetoggle">'.__("Informatie uitklappen").'</li>';
        print '<li class="down" id="slidetoggle" style="display:none;">'.__("Informatie inklappen").'</li>';
        print "</ul>";
        
    }
    
    public function filterFileMarkup($html, $args){
        if ($user = current_user()){
    		return $html;
    	}
        $file = $args['file'];
        if((metadata($file, array('Dublin Core', 'Rights')) == 'nee') || (metadata($file, array('Dublin Core', 'Relation')) == 'ja')){
            return '<p>Een bestand bevat auteursrechtelijke informatie of bevat extreme facetten. 
            <br>De inhoud is daarom afgeschermd, en kan alleen worden geraadpleegd op het Meertens Instituut, of met een admin account.<br><br></p>';
        }
        return $html;
    }

    /**
     * Add the data linker navigation link.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array('label' => __('Data linker'), 'uri' => url('data-linker'));
        return $nav;
    }

    /**
     * Add the data linker navigation link.
     */
    public function hookPublicHead($args)
    {
        clear_filters(array('Display', 'Item', 'Dublin Core', 'Title'));
        queue_css_file('linked'); // assumes myplugin has a /views/public/css/linked.css file
        queue_js_file('showHide');  
        $view = get_view();
        if(isset($view->item)) {
            add_filter(array('Display', 'Item', 'Dublin Core', 'Description'),                  'scroll_to_full_text');
            add_filter(array('Display', 'Item', 'Dublin Core', 'Subject'),                      'subject_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Language'),                     'language_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Type'),                         'type_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Creator'),                      'creator_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Collector'),             'collector_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Subgenre'),              'subgenre_info_retrieve_popup_jquery', 7);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'),                  'text_extreme_hide', 5);
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'),                  'text_copyright_hide', 6);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Source'),                       'make_urls_clickable_in_text');
            add_filter(array('Display', 'Item', 'Item Type Metadata', 'Kloeke georeference'),   'my_kloeke_link_function', 4);
            add_filter(array('Display', 'Item', 'Dublin Core', 'Date'),                         'present_dates_as_language', 20);
        }    
    }

    public function hookAdminHead($args)
    {
        add_filter(array('Display', 'Item', 'Dublin Core', 'Subject'),                      'subject_info_retrieve_box', 7);
#        queue_css_file('linked'); // assumes myplugin has a /views/public/css/linked.css file
#        add_filter(array('Display', 'Item', 'Dublin Core', 'Subject'), 'my_type_link_function_admin');
#        add_filter(array('Display', 'Item', 'Dublin Core', 'Source'), 'make_urls_clickable_in_text');
#        add_filter(array('Display', 'Item', 'Item Type Metadata', 'Kloeke georeference'), 'my_kloeke_link_function', 4);
        add_filter(array('Display', 'Item', 'Item Type Metadata', 'Text'), 'make_urls_clickable_in_text');
        add_filter(array('Display', 'Item', 'Dublin Core', 'Identifier'), 'all_items_with_this_subject', 10);
#        add_filter(array('Display', 'Item', 'Dublin Core', 'Description'), 'dummy_printer');
    }

    public function filterPublicNavigationMain($args){
        #ONLY FOR NAVIGATION
    }
    
    public function hookAdminItemsShow2($args)
    {
        if (metadata("item", 'Item Type Name') == "Volksverhaaltype"){
            $search_url = url(array('module'=>'items','controller'=>'browse'), 
                            'default', 
                            array("search" => "",
                                "submit_search" => "Zoeken",
                                "advanced[0][element_id]" => "49",
                                "advanced[0][type]" => "is exactly",
                                "advanced[0][terms]" => "$args",
                                )
                            );
            print "<a class='small blue advanced-search-link button' href='$search_url'>alle items van dit type</a>";
        }
    }
}


#add_filter(array('Display', 'Item', 'Item Type Metadata', 'Comments'), 'dummy_printer', 5);

function dummy_printer($args){
    return "DUMMY";
}

function make_urls_clickable_in_text($args){
    return preg_replace('#(\A|[^=\]\'"a-zA-Z0-9])(http[s]?://(.+?)/[^()<>\s]+)#i', '\\1<a target="linked" href="\\2">\\2</a>', $args);
#	return url_to_link($args);
}

function my_kloeke_link_function($args){
    $kloeke_link = "http://www.meertens.knaw.nl/kaart/v3/rest/?type=dutchlanguagearea&data[]=$args";
    return "<a href='$kloeke_link'>$args</a>";
}

function all_items_with_this_subject($args){
    if (metadata("item", 'Item Type Name') == "Volksverhaaltype"){
        $search_url = url(array('module'=>'items','controller'=>'browse'), 
                        'default', 
                        array("search" => "",
                            "submit_search" => "Zoeken",
                            "advanced[0][element_id]" => "49",
                            "advanced[0][type]" => "is exactly",
                            "advanced[0][terms]" => "$args",
                            )
                        );
        return "$args <a class='small blue advanced-search-link button' href='$search_url'>alle items van dit type</a>";
    }
    return $args;
}


function my_type_link_function_admin($args){
    if ($args){
        $type_information = get_type_info($args);
        $search_url = url(array('module'=>'items','controller'=>'browse'), 
                        'default', 
                        array("search" => "",
                            "submit_search" => "Zoeken",
                            "advanced[0][element_id]" => "49",
                            "advanced[0][type]" => "is exactly",
                            "advanced[0][terms]" => "$args",
                            )
                        );
        return "link";
        return "<a class='hover-type' href='$search_url'>$args</a> <span> - $type_information</span>";
    }
	return "No value";
}

/**
 * Return the site-wide search form.
 * 
 * @package Omeka\Function\Search
 * @param array $options Valid options are as follows:
 * - show_advanced (bool): whether to show the advanced search; default is false.
 * - submit_value (string): the value of the submit button; default "Submit".
 * - form_attributes (array): an array containing form tag attributes.
 * @return string The search form markup.
 */
function search_form_extended(array $options = array())
{
    return get_view()->searchForm($options);
}


?>