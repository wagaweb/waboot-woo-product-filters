import $ from "jquery";

class Dashboard{
    constructor(){
        this.ajax_endpoint = wbwpf.ajax_url;
        this.ajax_create_filters_table_action = "create_products_index_table";
        this.init();
    }

    init(){
        let $custom_table_form = $("#custom-table-parameters");
        if($custom_table_form.length > 0){
            this.handle_index_table_form();
        }
    }

    /**
     * Handle the form for the creation of index table
     */
    handle_index_table_form(){
        let $form = $("#custom-table-parameters");

        $form.on("submit",(e) => {
            e.preventDefault();
            //Collect data
            let table_params = {},
                $dataTypes_input = $("[data-datatype]").filter(":checked");
            if($dataTypes_input.length > 0){
                for(let input of $dataTypes_input){
                    let $input = $(input);
                    if(typeof table_params[""+$input.data("datatype")+""] === "undefined"){
                        debugger;
                        table_params[""+$input.data("datatype")+""] = [];
                    }
                    table_params[$input.data("datatype")].push($input.val());
                }
            }
            //Send ajax requests
            this.handle_index_table_creation(table_params);
        });
    }

    /**
     * Handle ajax cycle to create and fill the product index table with data
     *
     * @param table_params
     */
    handle_index_table_creation(table_params){
        let data = {
            current_percentage: 0,
            limit: 1,
            offset: 0,
            table_params: table_params
        };

        /*
         * Recursively call ajax endpoint
         */
        let do_req = (data) => {
            $.ajax({
                url: this.ajax_endpoint,
                data: {
                    action: this.ajax_create_filters_table_action,
                    params: data,
                },
                method: "POST"
            })
                .then(function(result,textStatus,jqX){
                    debugger;
                    switch(result.data.status){
                        case "run":
                            return do_req(result.data);
                            break;
                        case "complete":
                            return "complete";
                            break;
                    }

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

