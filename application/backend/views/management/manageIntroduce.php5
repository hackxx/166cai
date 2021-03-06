<?php $this->load->view('templates/head'); ?>

<div class="frame-container" style="margin-left:0;padding-left: 0px;">
    <div class="path">您的位置：运营管理&nbsp;&gt;&nbsp;<a href="/backend/Management/manageIntroduce/">个人简介</a></div>
    <div class="mod-tab-hd mt20">
        <ul>
            <li class="current"><a href="/backend/Management/manageIntroduce/">发起人简介</a></li>
            <li><a href="/backend/Management/unitedIntroduce/">合买宣言</a></li>
        </ul>
    </div>

    <div class="data-table-filter mt10" style="width:1100px">
        <form action="" method="get" onsubmit="return check()">
            <table>
                <colgroup>
                    <col width="62" />
                    <col width="150" />
                    <col width="62" />
                    <col width="300" />
                    <col width="62" />
                    <col width="200" />
                </colgroup>
                <tbody>
                    <tr>
                        <th></th>
                        <td>
                            <input type="checkbox" name="number" value="1" <?php
                            if ($number == '1'): echo "checked";
                            endif;
                            ?>>含数字
                        </td>
                        <th></th>
                        <td>
                            <input type="checkbox" name="words" value="1" <?php
                            if ($words == '1'): echo "checked";
                            endif;
                            ?>>含字母
                        </td>
                        <th></th>
                        <td>
                            <input type="checkbox" name="chinesenumer" value="1" <?php
                            if ($chinesenumer == '1'): echo "checked";
                            endif;
                            ?>>含中文数字
                        </td>
                        <th>审核状态：</th>
                        <td>
                            <select class="selectList w100 mr20"  name="check_status">
                                <option value="-1">不限</option>
                                <option value="0" <?php
                                    if ('0' === ($check_status)): echo "selected";
                                    endif;
                                    ?>>未审核</option>
                                <option value="1" <?php
                                    if ('1' === ($check_status)): echo "selected";
                                    endif;
                                    ?>>审核成功</option>  
                                <option value="2" <?php
                                    if ('2' === ($check_status)): echo "selected";
                                    endif;
                                    ?>>审核失败</option>  
                            </select>
                        </td>
                        <td>
                            <input type="text" class="ipt w222" name="name" value='<?php echo $name; ?>' placeholder="用户名" />
                            创建时间:<span class="ipt ipt-date w184"><input type="text" name='start_time' value="<?php echo $start_time; ?>" class="Wdate1" /><i></i></span>至
                            <span class="ipt ipt-date w184"><input type="text" name='end_time' value="<?php echo $end_time; ?>" class="Wdate1" /><i></i></span>
                            <button class="btn-blue mr20" type="submit">查询</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
    <div class="data-table-list mt20">
        <table>
            <colgroup>
                <col width="150" />
                <col width="500" />
                <col width="80" />
                <col width="150" />
                <col width="140" />
                <col width="110" />
            </colgroup>
            <thead>
                <tr>
                    <th>用户名</th>
                    <th>个人简介</th>
                    <th>审核状态</th>
                    <th>敏感词</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
            <?php $allstatus=array(0=>'未审核',1=>'审核成功',2=>'审核失败'); ?>
            <?php foreach ($users as $user){ ?>
            <tr>
                <td><a target="_blank" href="/backend/User/user_manage/?uid=<?php echo $user['uid'] ?>"
                       class="cBlue"><?php echo $user['uname'];?></a></td>
                <td style="word-break:break-word"><?php echo $user['introduction'];?></td>
                <td><?php echo $allstatus[$user['introduction_status']];?></td>
                <td><?php if( $user['introduction_status']==2 ): ?><?php echo $user['sensitive_words'];?><?php endif; ?></td>
                <td><?php echo $user['introduction_time'];?></td>
                <td>
                <?php if ($user['introduction_status']==3 ) {?>
                	已删除
                <?php }else {?>
                	<a href="javascript:void(0);" data-uid="<?php echo $user['uid']; ?>" data-name="<?php echo $user['uname'];?>" class="empty">删除</a>
                <?php }?>
                <?php if( $user['introduction_status']==2 ): ?>  
                    &nbsp;&nbsp;<a href="javascript:void(0);" data-uid="<?php echo $user['uid']; ?>" data-name="<?php echo $user['uname'];?>"  data-intro="<?php echo $user['introduction']; ?>" class="handPlay">手动成功</a>  
                <?php endif; ?>  
                </td>
            </tr>
            <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="11">
                        <div class="stat">
                            <span>本页&nbsp;<?php echo $pages[2] ?>&nbsp;条</span>
                            <span class="ml20">共&nbsp;<?php echo $pages[1] ?>&nbsp;页</span>
                            <span class="ml20">总计&nbsp;<?php echo $pages[3] ?>&nbsp;</span>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="page mt10 united_order">
        <?php echo $pages[0];?>
    </div>
    <div class="pop-dialog" id="alertPop">
        <div class="pop-in">
            <div class="pop-head">
                <h2>提示</h2>
                <span class="pop-close" title="关闭">关闭</span>
            </div>
            <div class="pop-body">
                <div class="data-table-filter del-percent" id="alertBody" style="text-align: center">
                </div>
            </div>
            <div class="pop-foot tac">
                <a href="javascript:;" class="btn-b-white mlr15 pop-cancel">确认</a>
            </div>
        </div>
    </div>
    <!--引入第三方插件 layer.js-->
    <script src="/caipiaoimg/src/layer/layer.js"></script>
    <script  src="/source/date/WdatePicker.js"></script>
    <script>
        //清空简介
        $(".Wdate1").focus(function () {
            dataPicker();
        });
        $('.empty').click(function(){
            var datas = {'uid':$(this).attr("data-uid"),'name':$(this).attr("data-name")};
            layer.open({
              'title':'清空简介',
              'type': 1,
              'area': '300px;',
              'closeBtn': 1, //不显示关闭按钮
              'btn': ['确认', '取消'],
              'shadeClose': true, //开启遮罩关闭
              'content': '<div style="margin-left:15px;margin-top:15px;margin-right:15px;">'+"是否清空 <span style='color:red;'>"+$(this).attr("data-name")+"</span> 的个人简介？"+'</div>', 
              'btnAlign': 'c',
              'yes': function()
                {
                    ajaxComm(datas,'/backend/Management/emptyIntroduce');
                    layer.load(0, {shade: [0.5, '#393D49']});
                }
            }); 
        });
        
        function check(){
            var start = $("input[name='start_time']").val();
            var end = $("input[name='end_time']").val();
            start=new Date(start.replace("-","/"));
            end=new Date(end.replace("-","/"));
            if(start>end){
                $("#alertBody").html('您选择的时间段错误，请核对后操作');
                popdialog("alertPop");
                return false;
            }
        }

        $('.handPlay').click(function(){
            var datas = {'uid':$(this).attr("data-uid"),'name':$(this).attr("data-name")};
            layer.open({
              'title':'手动成功',
              'type': 1,
              'area': '300px;',
              'closeBtn': 1, //不显示关闭按钮
              'btn': ['确认', '取消'],
              'shadeClose': true, //开启遮罩关闭
              'content': '<div style="margin-left:15px;margin-top:15px;margin-right:15px;">是否将评论 “<span style="color:red;">'+$(this).attr("data-intro")+'</span>” 置为成功？</div>', 
              'btnAlign': 'c',
              'yes': function()
                {
                    ajaxComm(datas,'/backend/Management/handSucc');
                    layer.load(0, {shade: [0.5, '#393D49']});
                }
            }); 
        });
    /**
     * [ajaxComm ajax公用方法]
     * @author LiKangJian 2017-08-24
     * @param  {[type]} datas [description]
     * @param  {[type]} url   [description]
     * @return {[type]}       [description]
     */
    function ajaxComm(datas,url)
    {
        $.ajax({
            type: "post",
            url: url,
            data: datas,
            success: function(data)
            {
                var json = jQuery.parseJSON(data);
                layer.closeAll();
                if(json.status == 'SUCCESSS'  || json.status == 'y' )
                {
                    layer.alert(json.message, {icon: 1,btn:'',title:'温馨提示',time:0,end:function(){location.reload();}});
                }else{
                    layer.alert(json.message, {icon: 2,btn:'',title:'温馨提示',time:0});
                }
            }
        })
    }
    </script>    
</body>
</html>