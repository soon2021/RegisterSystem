<?php
/*需要完善的地方
*添加字符串为空和正则表达式的校验
*完善相应的规则及命名
*封装使用到jq的方法
*/
session_start();
header("content-type:text/html;charset=utf-8");
require_once('../Config/class-object.php');
$querysql="select doc_id,doc_name,doc_photo,doc_profession,doc_savedate,doc_intro from docinfo";
$result=$dbcon->query($querysql);
$i=1;
?>
<!DOCTYPE html>
<html>
<head>
<?php
require_once('../Config/head.php');
?>
<script>
var subtype="";
$(function(){
/*
定义函数
1、模态框函数
2、提交表单函数
*/
/***********根据操作需求，获取模态框内容*********/
function getModal(gettype,senddata){
        $('#reserveDialogModal').modal({
          backdrop: 'static',
          //点击背景空白处不被关闭；
          keyboard: false
         //触发键盘esc事件时不关闭。
        });
        console.log("执行");
        $.ajax({
          type:"POST",
          //contentType:"application/x-www-form-urlencoded",
          url:"addDoctor.php",
          data:{type:gettype,data:senddata},
          datatype:"html",
          success:function(result){
                $("#reserveForm").html(result);
          },
          error:function(e){
            alert(e.status+e.responseText);
          }
        });
}
/***********根据操作需求，操作数据库*********/
function operateDocinfo(senddata){
    //    console.log("执行提交");
    //    console.log(senddata);
       $.ajax({
        type:"POST",
          contentType:"application/x-www-form-urlencoded",
          url:"editForm.php",
          data:senddata,
          datatype:"html",
          success:function(data){
            console.log(data);
            var resultdata=$.parseJSON(data);
            if(!resultdata.result)
            {
                alert(resultdata.result);
            }
            else
            {
                $('#reserveDialogModal').modal('hide');
                alert(resultdata.message);
                window.location.reload();
            }
          },
          error:function(e){
            alert(e.status+e.responseText);
          }
       });
   }

    
$("#reserveDialog").click(function(){getModal("add",'')});                                                                                                                      /*点击增加显示模态框*/
$("#saveBtn").click(function(){$("#docid").val()==''?subtype="add":subtype="edit";var formdata=$.param({type:subtype})+'&'+$("form").serialize();operateDocinfo(formdata)});    /*模态框点击确定，提交表单*/
$(".editinfo").click(function(){getModal("edit",$(this).attr('id').split('_')[1]);console.log($(this).attr('id').split('_')[1])});                                              /*编辑信息调出模态框*/
$(".deleteinfo").click(function(){if(confirm("删除后不可恢复，确认要删除吗？")){var data=$.param({type:"delete",docid:$(this).attr('id').split('_')[1]});operateDocinfo(data);}});/*删除信息*/
  /**********************************/
   $('body').on('change','#photofile',function(){
       console.log($("#photofile")[0].files);
       var files=$("#photofile")[0].files[0];
     if(files.size/1024/1024>6)
       {
           alert("文件大于6M,无法上传");
       }
       else{
       var uploadFile=new FormData();
       uploadFile.append('uploadFile',files);
       console.log(uploadFile);
       $.ajax({
           type:"POST",
           contentType: false,
           processData: false,
           url:"uploadFile.php",
           data:uploadFile,
           datatype:'json',
           success:function(data){
            console.log(data);
             $("#reserveForm #docphoto").attr("src",data.fileUrl);
             $("#reserveForm .docphoto").remove();
             $("#reserveForm #fileinfo").append( "<input type='hidden' class='docphoto' name='docphoto' value='"+data.fileUrl+"'></input>");
           },
           error: function (data, status, e)
        {
            console.log(data);
            alert(e);
        }
       });
    }
   });

});
</script>
<style type="text/css">
.docphoto{
    width:50px;
    height:50px;
}
</style>
</head>
<body>
<div class="container">
<div class="row">
<div class="col-12">
<button id="reserveDialog" class="btn btn-primary btn-large" href="#reserveDialogModal"  data-toggle="modal">增加</button>
</div>
</div>
</div> 
<div class="container">
<div class="row">
<div class="col-12">
<table class="table">
<tr>
<th>照片</th>
<th>姓名</th>
<th>职称</th>
<th>简介</th>
<th>录入时间</th>
<th>操作</th>
</tr>
<?php
while ($temp_result=$dbcon->fetch_assoc($result)) {
    extract($temp_result);
?>
<tr>
<td><img src=<?php echo $doc_photo;?> alt="" class="docphoto"></td>
<td><?php echo $doc_name;?></td>
<td><?php echo $doc_profession;?></td>
<td><?php echo $doc_intro;?></td>
<td><?php echo $doc_savedate;?></td>
<td><a id=<?php echo "editinfo_".$doc_id;?> class="editinfo" href="javascript:void(0);">编辑</a><a id=<?php echo "deleteinfo_".$doc_id;?> class="deleteinfo" href="javascript:void(0);">删除</a></td>
</tr>
<?php }
$dbcon->free_result($result);
$dbcon->close();
?>
</table>
</div>
</div>
</div>

<div class="modal fade"  style="" id="reserveDialogModal" tabindex="-1" aria-hidden="true" data-backdrop="static"  aria-labelledby="myModalLabel">
<div class="container">
<div class="row">
<div class="col-2">
</div>
<div class="col-8">
<div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">医生信息</h4>
                <!-- 
                    <button type="button" class="close" data-dismiss="modal">&times;</button> 
                    此处去掉右上方的叉叉，因为下方已经有“取消”按钮了，没必要再多一个
                -->
            </div>
            <div class="modal-body">
                <div class="reserve_top_line">
                </div>
                <div class="modal-body form-horizontal" id="reserveForm">
                    <!--信息表单 使用ajax请求另一个页面的表单-->

                    <!--end 信息表单-->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="closeBtn" class="btn btn-default btn-flat md-close" data-dismiss="modal">
                    取消
                </button>
                <button type="button" id="saveBtn" class="btn btn-primary btn-flat">确定</button>
            </div>
        </div>
    </div>
</div>
<div class="col-2">
</div>
</div>
</div>
</div>
</body>
</html>