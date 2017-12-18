<? /**
 * @var Blocks $block
 */ ?>
<script type="text/javascript">
    function ccRequest(data, callback){
        $.ajax({
            type: "POST",
            url: "<?= $this->createUrl('clientCode'); ?>",
            data: data,
                beforeSend : function() {
                    $("#clientCode").addClass("ajax-sending");
                },
                complete : function() {
                    $("#clientCode").removeClass("ajax-sending");
                },
            success: function(data) {
                console.log('loaded');
                $('#clientCode').html(data);
                if(typeof callback == "function") callback(data);
            }
        });

    }

    function updateControlLink(){
        $('#ccControl').attr(
            'href',
            "<?= $this->createUrl('clientCode', array('control' => 1)); ?>"
                + "&ClientCode%5Bpath%5D=" + encodeURIComponent($('#ClientCode_path').val())
                + "&ClientCode%5Bplatform_id%5D=" + encodeURIComponent($('#ClientCode_platform_id').val())
        );
    }
    jQuery(function($){
        $('#Blocks_platform_id').on('change', function(e){
            console.log('change', e);
            ccRequest({ClientCode: {platform_id: $('#Blocks_platform_id option:selected').val()}});
        });

        $('#Blocks_platform_id').trigger('change');

        $('#Blocks_use_client_code').on('change', function(e){
            if(this.checked){
                $('#clientCode').show();
                if(typeof ccValid != "undefined" && !ccValid){
                    $('#Blocks_controls button').prop('disabled', true);
                }else{
                    $('#Blocks_controls button').prop('disabled', false);
                }
            }else{
                $('#clientCode').hide();
                $('#Blocks_controls button').prop('disabled', false);
            }
            updateBlockCode();
        });

        $('#Blocks_use_client_code').change();
    });
</script>