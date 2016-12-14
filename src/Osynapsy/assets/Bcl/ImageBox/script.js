ImageBox =
{
    init : function ()
    {       
        $('.osy-imagebox').on('change','input[type=file]',function(e){          
            var filepath = this.value;
            var m = filepath.match(/([^\/\\]+)$/);
            var filename = m[1];
            $('.osy-imagebox-filename').text(filename);
            var uploadAction = $(this).closest('.osy-imagebox').data('action');
            setTimeout(function(){ 
                FormController.exec(uploadAction) 
            }, 1000);
        });
        $('.osy-imagebox-dummy').click(function(e){
           
            e.preventDefault();                                 
        });
        $('.osy-imagebox-cmd-crop').change(function(){
            div = $(this).closest('.osy-imagebox');
            iid = div.attr('id');
            if ($('.osy-imagebox-crop',div).val() == ''){
               return;
            }       
            dat = '&ajax='+iid;
            dat += '&ajax-cmd=crop';
            dat += '&'+iid+"_filename="+$('.osy-imagebox-preview',div).attr('src');
            dat += '&'+iid+"_coords="+$('#'+iid+'_crop').val();
            $.ajax({
             type : 'post',
             data : $('form').serialize() + dat,
             success : function(rsp){
                if (rsp == 'OK'){
                    FormController.reinit();
                } else {
                    alert(rsp);
                }
             }
            });
        });
        if ($('.image-crop > img.osy-imagebox-master').length > 0){
            $.getScript('/vendor/osynapsy/Bcl/ImageBox/jquery.Jcrop.min.js').done(function(){
               $('.osy-imagebox > img.osy-imagebox-master').Jcrop({
                    minSize: [278,278], 
                    maxSize:[278,278],
                    setSelect: [100,100,300,300],
                    onChange: ImageBox.show_coords,
                    onSelect: ImageBox.show_coords
               });
           });
        }
    },
    show_coords : function(c,e)
    {
        $('.osy-imagebox-crop').val(c.x +','+c.y+','+c.w+','+c.h);
        var rx = 138 / c.w;
        var ry = 138 / c.h;
        $('.osy-imagebox-preview').css({
                width: Math.round(rx * 1280) + 'px',
                height: Math.round(ry * 720) + 'px',
                marginLeft: '-' + Math.round(rx * c.x) + 'px',
                marginTop: '-' + Math.round(ry * c.y) + 'px'
        }).show();
    },
    delete : function(oid)
    {
        if (!confirm('Sei sciuro di voler eliminare l\'immagine?')) return;
        dat = $('form').serialize();
        dat += '&ajax='+oid;
        dat += '&ajax-cmd=delete';
        $.ajax({
            type : 'post',
            data : dat,
            context : $('#'+oid).parent(),
            success : function(rsp) {
                if (rsp == 'OK'){
                    $('label.osy-imagebox-dummy',this).html('<span class="fa fa-camera"></span>');
                    $('div.osy-imagebox-cmd',this).remove();                
                } else {
                    alert(rsp);
                }
            }
        });
    }
}

if (window.FormController) {
    FormController.register('init','ImageBox',function() {
        ImageBox.init();
    });
}
