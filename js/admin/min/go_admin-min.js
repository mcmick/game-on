function go_hide_child_tax_acfs(){-1==jQuery(".taxonomy-task_chains #parent, .taxonomy-go_badges #parent").val()?jQuery(".go_child_term").hide():jQuery(".go_child_term").show()}function go_update_go_ajax(){var e=GO_EVERY_PAGE_DATA.nonces.go_upgade4;jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:e,action:"go_upgade4",loot:!0},success:function(e){alert("Done.  Hope that helps :)")}})}function go_update_go_ajax_no_task_loot(){var e=GO_EVERY_PAGE_DATA.nonces.go_upgade4;jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:e,action:"go_upgade4",loot:!1},success:function(e){alert("Done.  Hope that helps :)")}})}function go_sounds(e){if("store"==e){var a=new Audio(PluginDir.url+"media/gold.mp3");a.play()}else if("timer"==e){var a=new Audio(PluginDir.url+"media/airhorn.mp3");a.play()}}function go_admin_bar_stats_page_button(e){var a=GO_EVERY_PAGE_DATA.nonces.go_admin_bar_stats;jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:a,action:"go_admin_bar_stats",uid:e},success:function(e){-1!==e&&(jQuery.featherlight(e,{variant:"stats"}),go_stats_task_list(),jQuery("#stats_tabs").tabs(),jQuery(".stats_tabs").click(function(){switch(tab=jQuery(this).attr("tab"),tab){case"about":go_stats_about();break;case"tasks":go_stats_task_list();break;case"store":go_stats_item_list();break;case"history":go_stats_activity_list();break;case"badges":go_stats_badges_list();break;case"groups":go_stats_groups_list();break;case"leaderboard":go_stats_leaderboard();break}}))}})}function go_stats_about(e){console.log("about");var a=GO_EVERY_PAGE_DATA.nonces.go_stats_about;0==jQuery("#go_stats_about").length&&jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:a,action:"go_stats_about",user_id:jQuery("#go_stats_hidden_input").val()},success:function(e){-1!==e&&(console.log(e),console.log("about me"),jQuery("#stats_about").html(e))}})}function go_stats_task_list(){jQuery("#go_task_list_single").remove(),jQuery("#go_task_list").show(),jQuery("#go_tasks_datatable").DataTable().columns.adjust().draw();var e=GO_EVERY_PAGE_DATA.nonces.go_stats_task_list;0==jQuery("#go_tasks_datatable").length&&jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:e,action:"go_stats_task_list",user_id:jQuery("#go_stats_hidden_input").val()},success:function(e){-1!==e&&(jQuery("#stats_tasks").html(e),jQuery("#go_tasks_datatable").dataTable({responsive:!0,autoWidth:!1,order:[[jQuery("th.go_tasks_timestamps").index(),"desc"]],columnDefs:[{targets:"go_tasks_reset",sortable:!1}]})),console.log("everypage");var a=jQuery("#go_stats_messages_icon").attr("name");jQuery(".go_reset_task").one("click",function(e){go_messages_opener(a,this.id,"reset")})}})}function go_stats_single_task_activity_list(e){var a=GO_EVERY_PAGE_DATA.nonces.go_stats_single_task_activity_list;jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:a,action:"go_stats_single_task_activity_list",user_id:jQuery("#go_stats_hidden_input").val(),postID:e},success:function(e){-1!==e&&(jQuery("#go_task_list_single").remove(),jQuery("#go_task_list").hide(),jQuery("#stats_tasks").append(e),jQuery("#go_single_task_datatable").dataTable({bPaginate:!0,order:[[0,"desc"]],responsive:!0,autoWidth:!1}))}})}function go_stats_item_list(){console.log("store");var e=GO_EVERY_PAGE_DATA.nonces.go_stats_item_list;0==jQuery("#go_store_datatable").length&&jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:e,action:"go_stats_item_list",user_id:jQuery("#go_stats_hidden_input").val()},success:function(e){-1!==e&&(jQuery("#stats_store").html(e),jQuery("#go_store_datatable").dataTable({bPaginate:!0,order:[[0,"desc"]],responsive:!0,autoWidth:!1}))}})}function go_stats_activity_list(){var e=GO_EVERY_PAGE_DATA.nonces.go_stats_activity_list;0==jQuery("#go_activity_datatable").length&&jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:e,action:"go_stats_activity_list",user_id:jQuery("#go_stats_hidden_input").val()},success:function(e){-1!==e&&(jQuery("#stats_history").html(e),jQuery("#go_activity_datatable").dataTable({processing:!0,serverSide:!0,ajax:{url:MyAjax.ajaxurl+"?action=go_activity_dataloader_ajax",data:function(e){e.user_id=jQuery("#go_stats_hidden_input").val()}},responsive:!0,autoWidth:!1,columnDefs:[{targets:"_all",orderable:!1}],searching:!0}))}})}function go_stats_badges_list(){var e=GO_EVERY_PAGE_DATA.nonces.go_stats_badges_list;0==jQuery("#go_badges_list").length&&jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:e,action:"go_stats_badges_list",user_id:jQuery("#go_stats_hidden_input").val()},success:function(e){-1!==e&&jQuery("#stats_badges").html(e)}})}function go_stats_groups_list(){var e=GO_EVERY_PAGE_DATA.nonces.go_stats_groups_list;0==jQuery("#go_groups_list").length&&jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:e,action:"go_stats_groups_list",user_id:jQuery("#go_stats_hidden_input").val()},success:function(e){-1!==e&&jQuery("#stats_groups").html(e)}})}function go_sort_leaders(e,a){var t,s,o,_,r,n,i;for(t=document.getElementById(e),o=!0,console.log("switching");o;){for(o=!1,s=t.getElementsByTagName("TR"),_=1;_<s.length-1;_++)if(i=!1,r=s[_].getElementsByTagName("TD")[a],xVal=r.innerHTML,n=s[_+1].getElementsByTagName("TD")[a],yVal=n.innerHTML,parseInt(xVal)<parseInt(yVal)){i=!0;break}i&&(s[_].parentNode.insertBefore(s[_+1],s[_]),o=!0)}}function go_filter_datatables(){jQuery.fn.dataTable.ext.search.push(function(e,a,t){var s=e.sTableId;if("go_clipboard_datatable"==s||"go_clipboard_messages_datatable"==s||"go_clipboard_activity_datatable"==s){var o=jQuery("#go_clipboard_user_go_sections_select").val(),_=jQuery("#go_clipboard_user_go_groups_select").val(),r=jQuery("#go_clipboard_go_badges_select").val(),n=a[3],i=a[2],l=a[1];i=JSON.parse(i),n=JSON.parse(n);var u=!0;return u="none"==_||-1!=jQuery.inArray(_,i),u&&(u="none"==o||l==o),"go_clipboard_datatable"==s&&u&&(u="none"==r||-1!=jQuery.inArray(r,n)),u}if("go_xp_leaders_datatable"==s||"go_gold_leaders_datatable"==s||"go_c4_leaders_datatable"==s||"go_badges_leaders_datatable"==s){var o=jQuery("#go_user_go_sections_select").val(),_=jQuery("#go_user_go_groups_select").val(),i=a[2],l=a[1];i=JSON.parse(i),l=JSON.parse(l);var u=!0;return u="none"==_||-1!=jQuery.inArray(_,i),u&&(u="none"==o||-1!=jQuery.inArray(o,l)),"go_clipboard_datatable"==s&&u&&(u="none"==r||-1!=jQuery.inArray(r,n)),u}return!0})}function go_stats_leaderboard(){jQuery("#go_stats_lite_wrapper").remove(),jQuery("#go_leaderboard_wrapper").show(),go_filter_datatables();var e=GO_EVERY_PAGE_DATA.nonces.go_stats_leaderboard;0==jQuery("#go_leaderboard_wrapper").length&&(jQuery(".go_leaderboard_wrapper").show(),jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:e,action:"go_stats_leaderboard",user_id:jQuery("#go_stats_hidden_input").val()},success:function(e){console.log("success");var a={};try{var a=JSON.parse(e)}catch(e){console.log("parse_error")}if(jQuery("#stats_leaderboard").html(a.html),console.log("________XP___________"),jQuery("#go_xp_leaders_datatable").length){go_sort_leaders("go_xp_leaders_datatable",4);var t=jQuery("#go_xp_leaders_datatable").DataTable({orderFixed:[[4,"desc"]],responsive:!0,autoWidth:!1,paging:!1,columnDefs:[{targets:[1],visible:!1},{targets:[2],visible:!1}],createdRow:function(e,a,t){jQuery(e).find("td:eq(0)").text(t+1)}})}if(jQuery("#go_gold_leaders_datatable").length){go_sort_leaders("go_gold_leaders_datatable",4);var s=jQuery("#go_gold_leaders_datatable").DataTable({paging:!1,orderFixed:[[4,"desc"]],responsive:!0,autoWidth:!1,columnDefs:[{targets:[1],visible:!1},{targets:[2],visible:!1}],createdRow:function(e,a,t){jQuery(e).find("td:eq(0)").text(t+1)}})}if(jQuery("#go_c4_leaders_datatable").length){go_sort_leaders("go_c4_leaders_datatable",4);var o=jQuery("#go_c4_leaders_datatable").DataTable({paging:!1,orderFixed:[[4,"desc"]],responsive:!0,autoWidth:!1,columnDefs:[{targets:[1],visible:!1},{targets:[2],visible:!1}],createdRow:function(e,a,t){jQuery(e).find("td:eq(0)").text(t+1)}})}if(jQuery("#go_badges_leaders_datatable").length){go_sort_leaders("go_badges_leaders_datatable",4);var _=jQuery("#go_badges_leaders_datatable").DataTable({paging:!1,orderFixed:[[4,"desc"]],responsive:!0,autoWidth:!1,columnDefs:[{targets:[1],visible:!1},{targets:[2],visible:!1}],createdRow:function(e,a,t){jQuery(e).find("td:eq(0)").text(t+1)}})}jQuery("#go_user_go_sections_select, #go_user_go_groups_select").change(function(){jQuery("#go_xp_leaders_datatable").length&&t.draw(),jQuery("#go_gold_leaders_datatable").length&&s.draw(),jQuery("#go_c4_leaders_datatable").length&&o.draw(),jQuery("#go_badges_leaders_datatable").length&&_.draw()})}}))}function go_stats_lite(e){var a=GO_EVERY_PAGE_DATA.nonces.go_stats_lite;jQuery.ajax({type:"post",url:MyAjax.ajaxurl,data:{_ajax_nonce:a,action:"go_stats_lite",uid:e},success:function(e){-1!==e&&(jQuery("#go_stats_lite_wrapper").remove(),jQuery("#stats_leaderboard").append(e),jQuery("#go_leaderboard_wrapper").hide(),jQuery("#go_tasks_datatable_lite").dataTable({destroy:!0,responsive:!0,autoWidth:!1}))}})}function decimalAdjust(e,a,t){return void 0===t||0==+t?Math[e](a):(a=+a,t=+t,isNaN(a)||"number"!=typeof t||t%1!=0?NaN:(a=a.toString().split("e"),a=Math[e](+(a[0]+"e"+(a[1]?+a[1]-t:-t))),a=a.toString().split("e"),+(a[0]+"e"+(a[1]?+a[1]+t:t))))}function go_lb_opener(e){if(jQuery("#light").css("display","block"),jQuery(".go_str_item").prop("onclick",null).off("click"),"none"==jQuery("#go_stats_page_black_bg").css("display")&&jQuery("#fade").css("display","block"),!jQuery.trim(jQuery("#lb-content").html()).length){var a=e,t=GO_EVERY_PAGE_DATA.nonces.go_the_lb_ajax,s={action:"go_the_lb_ajax",_ajax_nonce:t,the_item_id:a},o="<?php echo admin_url( '/admin-ajax.php' ); ?>";jQuery.ajax({url:MyAjax.ajaxurl,type:"POST",data:s,beforeSend:function(){jQuery("#lb-content").append('<div class="go-lb-loading"></div>')},cache:!1,success:function(e){jQuery("#lb-content").innerHTML="",jQuery("#lb-content").html(""),jQuery.featherlight(e,{variant:"store"}),jQuery(".go_str_item").one("click",function(e){go_lb_opener(this.id)}),window.go_purchase_limit=jQuery("#golb-fr-purchase-limit").attr("val"),window.go_store_debt_enabled="true"===jQuery(".golb-fr-boxes-debt").val();var a=go_purchase_limit;jQuery("#go_qty").spinner({max:a,min:1,stop:function(){jQuery(this).change()}})}})}}function goBuytheItem(e,a){var t=GO_BUY_ITEM_DATA.nonces.go_buy_item,s=GO_BUY_ITEM_DATA.userID;console.log(s),jQuery(document).ready(function(a){var o={_ajax_nonce:t,action:"go_buy_item",the_id:e,qty:a("#go_qty").val(),user_id:s};a.ajax({url:MyAjax.ajaxurl,type:"POST",data:o,beforeSend:function(){a("#golb-fr-buy").innerHTML="",a("#golb-fr-buy").html(""),a("#golb-fr-buy").append('<div id="go-buy-loading" class="buy_gold"></div>')},success:function(e){var t={};try{var t=JSON.parse(e)}catch(e){t={json_status:"101",html:"101 Error: Please try again."}}-1!==e.indexOf("Error")?a("#light").html(e):a("#light").html(t.html)}})})}function go_count_item(e){var a=GO_BUY_ITEM_DATA.nonces.go_get_purchase_count;jQuery.ajax({url:MyAjax.ajaxurl,type:"POST",data:{_ajax_nonce:a,action:"go_get_purchase_count",item_id:e},success:function(e){if(-1!==e){var a=e.toString();jQuery("#golb-purchased").html("Quantity purchased: "+a)}}})}function go_messages_opener(e,a,t){if(console.log(t),jQuery(".go_messages_icon").prop("onclick",null).off("click"),jQuery("#go_stats_messages_icon").prop("onclick",null).off("click"),jQuery(".go_reset_task").prop("onclick",null).off("click"),jQuery("#go_blog_messages_icon").prop("onclick",null).off("click"),e)var s=[e];else for(var o=jQuery(".go_checkbox:visible"),s=[],_=0;_<o.length;_++)!0===o[_].checked&&s.push(jQuery(o[_]).val());var r=GO_EVERY_PAGE_DATA.nonces.go_create_admin_message,n={action:"go_create_admin_message",_ajax_nonce:r,post_id:a,user_ids:s,message_type:t};jQuery.ajax({url:MyAjax.ajaxurl,type:"POST",data:n,success:function(e){jQuery.featherlight(e,{variant:"message"}),jQuery(".go_tax_select").select2(),jQuery("#go_message_submit").one("click",function(e){go_send_message(s,a,t)}),jQuery(".go_messages_icon").one("click",function(e){go_messages_opener()});var o=jQuery("#go_stats_messages_icon").attr("name");jQuery("#go_stats_messages_icon").one("click",function(e){go_messages_opener(o)}),jQuery(".go_reset_task").one("click",function(e){go_messages_opener(o,this.id,"reset")});var o=jQuery("#go_blog_messages_icon").attr("name");jQuery("#go_blog_messages_icon").one("click",function(e){go_messages_opener(o)})},error:function(e,a,t){jQuery(".go_messages_icon").one("click",function(e){go_messages_opener()});var s=jQuery("#go_stats_messages_icon").attr("name");jQuery("#go_stats_messages_icon").one("click",function(e){go_messages_opener(s)}),jQuery(".go_reset_task").one("click",function(e){go_messages_opener(s,this.id,"reset")});var s=jQuery("#go_blog_messages_icon").attr("name");jQuery("#go_blog_messages_icon").one("click",function(e){go_messages_opener(s)})}})}function go_send_message(e,a,t){var s=jQuery("[name=title]").val(),o=jQuery("[name=message]").val(),_=jQuery("[name=xp_toggle]").siblings().hasClass("-on")?1:-1,r=jQuery("[name=xp]").val()*_,n=jQuery("[name=gold_toggle]").siblings().hasClass("-on")?1:-1,i=jQuery("[name=gold]").val()*n,l=jQuery("[name=health_toggle]").siblings().hasClass("-on")?1:-1,u=jQuery("[name=health]").val()*l,g=jQuery("[name=c4_toggle]").siblings().hasClass("-on")?1:-1,c=jQuery("[name=c4]").val()*g,d=jQuery("#go_messages_go_badges_select").val(),y=jQuery("[name=badges_toggle]").siblings().hasClass("-on"),j=jQuery("#go_messages_user_go_groups_select").val(),p=jQuery("[name=groups_toggle]").siblings().hasClass("-on"),b=GO_EVERY_PAGE_DATA.nonces.go_send_message,Q={action:"go_send_message",_ajax_nonce:b,post_id:a,user_ids:e,message_type:t,title:s,message:o,xp:r,gold:i,health:u,c4:c,badges_toggle:y,badges:d,groups_toggle:p,groups:j};jQuery.ajax({url:MyAjax.ajaxurl,type:"POST",data:Q,success:function(e){jQuery("#go_messages_container").html("Message sent successfully."),jQuery("#go_tasks_datatable").remove(),go_stats_task_list(),go_toggle_off()},error:function(e,a,t){jQuery("#go_messages_container").html("Error.")}})}jQuery("input,select").bind("keydown",function(e){13===(e.keyCode||e.which)&&(e.preventDefault(),jQuery("input, select, textarea")[jQuery("input,select,textarea").index(this)+1].focus())}),jQuery(document).ready(function(){go_hide_child_tax_acfs(),jQuery(".taxonomy-task_chains #parent, .taxonomy-go_badges #parent").change(function(){-1==jQuery(this).val()?jQuery(".go_child_term").hide():jQuery(".go_child_term").show()});var e=jQuery("#post_ID").val();jQuery("#go_store_item_id .acf-input").html('[go_store id="'+e+'"]')}),jQuery(document).ready(function(){jQuery("#go_tool_update").click(go_update_go_ajax),jQuery("#go_tool_update_no_loot").click(go_update_go_ajax_no_task_loot)}),String.prototype.getMid=function(e,a){if("string"==typeof e&&"string"==typeof a){var t=e.length,s=this.length-(e.length+a.length);return this.substr(t,s)}},Math.round10||(Math.round10=function(e,a){return decimalAdjust("round",e,a)}),Math.floor10||(Math.floor10=function(e,a){return decimalAdjust("floor",e,a)}),Math.ceil10||(Math.ceil10=function(e,a){return decimalAdjust("ceil",e,a)}),jQuery.prototype.go_prev_n=function(e,a){if(void 0===e)return null;"int"!=typeof e&&(e=Number.parseInt(e));for(var t=null,s=0;s<e;s++)if(0===s)t=void 0!==a?jQuery(this).prev(a):jQuery(this).prev();else{if(null===t)break;t=void 0!==a?jQuery(t).prev(a):jQuery(t).prev()}return t},jQuery(document).ready(function(){jQuery(".go_str_item").one("click",function(e){go_lb_opener(this.id)})});