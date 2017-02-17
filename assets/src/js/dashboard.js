import $ from "jquery";

class Dashboard{
    static init(){
        let $custom_table_form = $("#custom-table-parameters");
        if($custom_table_form.length > 0){
            Dashboard.handle_custom_table_form();
        }
    }

    static handle_custom_table_form(){
        let $form = $("#custom-table-parameters");
        $form.on("submit",function(e){
            e.preventDefault();
            //Collect data
            let data = {
                    taxonomies: [],
                    metas: []
                },
                $taxonomies_input = $("[name='wbwpf_use_tax[]']").filter(":checked"),
                $metas_input = $("[name='wbwpf_use_meta[]']").filter(":checked");
            if($taxonomies_input.length > 0){
                for(let input of $taxonomies_input){
                    let $input = $(input);
                    data.taxonomies.push($input.val())
                }
            }
            if($metas_input.length > 0){
                for(let input of $metas_input){
                    let $input = $(input);
                    data.metas.push($input.val());
                }
            }
            debugger;
        });
    }
}

$(document).ready(function($){
    Dashboard.init();
});

