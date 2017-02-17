import $ from "jquery";

class Dashboard{
    constructor(){
        this.ajax_endpoint = wbwpf.ajax_url;
        this.ajax_create_filters_table_action = "create_filters_table";
        this.init();
    }

    init(){
        let $custom_table_form = $("#custom-table-parameters");
        if($custom_table_form.length > 0){
            this.handle_custom_table_form();
        }
    }

    handle_custom_table_form(){
        let $form = $("#custom-table-parameters");


        $form.on("submit",(e) => {
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
            this.handle_custom_table_creation(data);
        });
    }

    handle_custom_table_creation(data){
        $.extend(data,{
            current_percentage: 0,
            limit: 0,
            offset: 1
        });

        let do_req = (data) => {
            $.ajax({
                url: this.ajax_endpoint,
                data: {
                    action: this.ajax_create_filters_table_action,
                    params: data,
                },
                method: "POST"
            })
                .then(function(data,textStatus,jqX){
                    debugger;
                    return do_req();
                })
                .fail(function(jqXHR,textStatus,errorThrown){
                    debugger;
                    return "failed";
                });
        };

        return do_req(data);
    }
}

$(document).ready(function($){
    new Dashboard();
});

