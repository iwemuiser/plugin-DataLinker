<?php
function subject_info_retrieve_box($args){
    if ($args){
        $taletype_search_url = url(array('module'=>'items','controller'=>'browse'), 'default', 
                                array("search" => "",
                                    "submit_search" => "Zoeken",
                                    "advanced[0][element_id]" => "49",
                                    "advanced[0][type]" => "is exactly",
                                    "advanced[0][terms]" => "$args"));
        $tale_view_url = get_element_by_value($args, "Identifier") ? record_url(get_element_by_value($args, "Identifier"), 'show') : "";
        $type_information = "Temporarily no title information"; ## REPLACE BY THIS:  $type_information = get_type_description($args);
        $pasted_args = str_replace(array(" ", "\r"), "", $args);
        $html = 
            '<table>
                <tr><td>'. $args . '</td>
                <td><a href="'.$taletype_search_url.'">Alle verhalen van het type '.$args.'</a> <br/>
                <a href="'.$tale_view_url.'">Alle informatie over type '.$args.'</a>
                </td>
                <tr>
                    <th colspan="2">' . 
                        $type_information . '<br />
                    </th>
                </tr>
            </table>';
        return $html;
   }
   return "something went wrong";
}
?>