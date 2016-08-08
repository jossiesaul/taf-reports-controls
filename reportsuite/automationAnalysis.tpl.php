<br />

<center>
    <div class="pageSubLink">
        <? echo l('2012', 'reportsuite/mainPage/Analysis/year/2012'); ?> |
        <? echo l('2013', 'reportsuite/mainPage/Analysis/year/2013'); ?> |
        <? echo l('2014', 'reportsuite/mainPage/Analysis/year/2014'); ?> |
        <? echo l('2015', 'reportsuite/mainPage/Analysis/year/2015'); ?>
    </div>


<br />

<? foreach(reportsuiteanalysishelper::monthInformation() as $k=>$v):?>
<? echo l($k, 'reportsuite/mainPage/Analysis/monthYear/'.$k.'-'.$data['timeQueried']['year']); ?> |
<?  endforeach;?>
</center>
<div>
<? if(isset($data['analysis']['statistical']['chart']['roi']))
print $data['analysis']['statistical']['chart']['roi'];?>
</div>
