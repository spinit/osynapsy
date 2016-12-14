BclTags = {
    init : function() {        
        $('.bclTags').on('click','.bclTags-add',function(){
            if ($(this).hasClass('cmd-execute')){
                return;
            }
            if (!$(this).data('fields')) {
                alert('Attributo data-fields non presente');
                return;
            }
            var fld = $(obj).data('fields').split(',');
            var lst = [];
            for (i in fld) {
                if ($(fld[i]).val() == '') {
                    alert('Non hai inserito nessun valore impossibile proseguire');
                    return;
                }
                lst.append($(fld[i]).val());
            }
            BclTags.addLabel(lst, $(this).data('parent'));
            $(this).closest('modal').modal('hide');
        }).on('click','.bclTags-delete',function(){
            BclTags.deleteLabel($(this));
        });
        $('.bclTags .dropdown li').click(function(e){
            BclTags.addLabel($(e.target).text(), '#'+$(e.target).closest('.bclTags').attr('id'));
            $(e.target).closest('.dropdown').removeClass('open');
        });
    },
    addLabel : function(lbl, par){
        lbl = '<span class="label label-info m-r-1" data-parent="'+par+'">' + lbl;
        lbl += ' <span class="fa fa-close bclTags-delete"></span>';
        lbl += '</span>';
        $('.bclTags-container', $('div'+par)).append(lbl);
        this.updateField(par);
    },
    deleteLabel : function(obj) {
        if (confirm('Sei sicuro di voler eliminare il tag ')) {            
            var par = $(obj).parent().data('parent');
            $(obj).parent().remove();
            this.updateField(par);
        }
    },
    updateField : function(par) {
        var val = '';
        $('.label', $('div'+par)).each(function(){
            val += (val != '' ? ';' : '') + $(this).text().trim();
        });
        $('input'+par).val(val);
    }
}

if (window.FormController){    
    FormController.register('init','BclTags',function(){
        BclTags.init();
    });
}