jQuery(document).ready(function($){
        $('.emd-import').click(function(e){
                $('#'+ imp_vars.action).click();
        });
        $('#'+imp_vars.action).change(function(e){
                fname = $(this).val();
                var file_data = $(this).prop('files')[0];
                var myd = new FormData();
                myd.append('file', file_data);
                myd.append('action', imp_vars.action);
                myd.append('fname', fname);
                myd.append('app', $(this).data('app'));
                myd.append('nonce',imp_vars.nonce);
                $.ajax({
                        type : 'POST',
                        url: imp_vars.ajax_url,
                        contentType: false,
                        processData: false,
                        data: myd,
                        success : function(resp) {
                                if(resp){
                                        //refresh page
                                        window.location.href = imp_vars.ret_url;
                                }
                                else {
                                        alert(imp_vars.import_err);
                                }
                        }
                });
        });
});
