<h1>Tests result:</h1>
<div style="width: 70%">
<div style="background-color: #FFED89; padding: 10px 10px 10px 10px">
<?=$cmd?>
</div>
<div style="background-color: black; color: white;">
<?foreach($output as $string):?>
    <?if($string == '.'):?>
    <div style="padding-top: 5px;">
    <span style="background-color: #00CE29; padding-left: 2px; padding-right: 2px;"><?=$string?></span>
    </div>
    <?elseif($string=='F'):?>
    <div style="padding-top: 5px;>
    <span style="background-color: red; padding-left: 2px; padding-right: 2px;"><?=$string?></span>
    </div>
    <?else:?>
    <div style="padding-top: 5px;">
    <?=$string?>
    </div>
    <?endif;?>
<?endforeach;?>
</div>
</div>