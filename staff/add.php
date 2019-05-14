<?php
include_once "../common/checkback.php";
$table = explode('/', $_SERVER['PHP_SELF'])[1];
//获取表字段名
$sql = "select COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS where table_name = '{$table}'";
$stmt = $pdo->query($sql);
$COLUMN_NAME = $stmt->fetchAll(PDO::FETCH_COLUMN);
//获取表字段注释
$sql = "select COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS where table_name = '{$table}'";
$stmt = $pdo->query($sql);
$COLUMN_COMMENT = $stmt->fetchAll(PDO::FETCH_COLUMN);


?>
<!DOCTYPE html>
<html>
<!-- Mirrored from www.zi-han.net/theme/hplus/projects.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 20 Jan 2016 14:19:44 GMT -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>添加新<?php echo $table?></title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/css/animate.min.css" rel="stylesheet">
    <link href="/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <script src="/ueditor/ueditor.config.js"></script>
    <script src="/ueditor/ueditor.all.min.js"></script>
    <script src="/ueditor/lang/zh-cn/zh-cn.js"></script>
    <style>
        input {
            width: 38px;
        }
        input[type=radio] {
            margin-top: 1px;
            margin-left: -25px;
        }
    </style>
</head>
<body class="gray-bg">
<div class="wrapper wrapper-content animated fadeInUp">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox float-e-margins">
                    <div class="ibox-title">
                        <h5>添加新<?php echo $table?></h5>
                        <div class="ibox-tools">
                            <a href="list.php" class="btn btn-primary btn-xs">返回列表</a>
                        </div>
                    </div>
                    <div class="ibox-content">
                        <form method="post" action="action.php?action=add" class="form-horizontal" >
                            <?php $index=0;foreach ($COLUMN_COMMENT as $i) : ?>
                                <?php
                                $sql="select * from config where TableName='{$table}' and ColumnName='{$COLUMN_NAME[$index]}' and UserTypeId='{$_SESSION['backuser']['UserTypeId']}'";
                                $stmt = $pdo->query($sql);
                                if ($stmt->rowCount() > 0) {
                                    $cf = $stmt->fetch(PDO::FETCH_ASSOC);
                                } else {
                                    $cf = [];
                                }
                                ?>

                                <?php if(!isset($cf['EditType'])||$cf['EditType']=='input'):?>
                                    <div class="form-group" <?php if($cf['Visible']==0){echo "style='display:none'";}?>>
                                        <label class="col-sm-2 control-label"><?php echo $i; ?></label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" name="<?php echo $COLUMN_NAME[$index]; ?>" <?php if($cf['Changeable']==0){echo "disabled='disabled'";}?>  >
                                        </div>
                                    </div>
                                    <div class="hr-line-dashed"  <?php if($cf['Visible']==0){echo "style='display:none'";}?>></div>
                                <?php endif;?>

                                <?php if($cf['EditType']=='select'):?>
                                    <?php
                                    $sql="select {$cf['FromColumn']},{$cf['FromColumnShow']} from {$cf['FromTable']}";
                                    $stmt = $pdo->query($sql);
                                    if ($stmt->rowCount() > 0) {
                                        $its = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    } else {
                                        $its = [];
                                    }
                                    ?>
                                    <div class="form-group" <?php if($cf['Visible']==0){echo "style='display:none'";}?> >
                                        <label class="col-sm-2 control-label"><?php echo $i; ?></label>
                                        <div class="col-sm-10">
                                            <select class="form-control m-b" name="<?php echo $COLUMN_NAME[$index]; ?>" id="<?php echo $COLUMN_NAME[$index]; ?>" <?php if($cf['Changeable']==0){echo "disabled='disabled'";}?>  style="height: 32px;" onchange="tablechanged()">
                                                <?php foreach ($its as $it): ?>
                                                    <option value='<?php echo $it[$cf['FromColumn']]?>' ><?php echo $it[$cf['FromColumn']].":".$it[$cf['FromColumnShow']]?></option>
                                                <?php endforeach;?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="hr-line-dashed" <?php if($cf['Visible']==0){echo "style='display:none'";}?>></div>
                                <?php endif;?>



                                <?php $index++;endforeach; ?>
                            <div class="form-group">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <button class="btn btn-primary" type="submit">保存</button>
                                    <button class="btn btn-white" type="reset">重置</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="/js/jquery.min.js"></script>
<script src="/js/jquery.form.min.js"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/content.min.js?v=1.0.0"></script>
<script src="/js/resize.js"></script>

<script>
    UE.getEditor('editor', {
        initialFrameWidth: 500, initialFrameHeight: 300, autoHeightEnabled: false,
        toolbars: [['fullscreen', 'source', 'undo', 'redo', 'simpleupload', 'insertimage']]
    });


    function preview(obj){
        var img = document.getElementById("previewimg");
        img.src = window.URL.createObjectURL(obj.files[0]);
    }

    $(document).ready(function () {
        $("#loading-example-btn").click(function () {
            btn = $(this);
            simpleLoad(btn, true);
            simpleLoad(btn, false)
        })
    });
    function simpleLoad(btn, state) {
        if (state) {
            btn.children().addClass("fa-spin");
            btn.contents().last().replaceWith(" Loading")
        } else {
            setTimeout(function () {
                btn.children().removeClass("fa-spin");
                btn.contents().last().replaceWith(" Refresh")
            }, 2000)
        }
    }

</script>

<script type="text/javascript">



    $(function(){
        var $iosDialog1 = $('#iosDialog1');
        $('#dialogs').on('click', '.weui-dialog__btn', function(){
            $(this).parents('.js_dialog').fadeOut(200);
        });
        $('#childadd').on('click', function(){
            $iosDialog1.show();
        });
    });




</script>

<script type="text/javascript" class="uploader js_show">
    function  addchild() {

    }
    $(function(){
        var $iosDialog1 = $('#iosDialog1');
        $('#dialogs').on('click', '.weui-dialog__btn', function(){
            $(this).parents('.js_dialog').fadeOut(200);
        });
        $('#childadd').on('click', function(){
            $iosDialog1.show();
        });
    });

    var options = {
//        target: '#output',          //把服务器返回的内容放入id为output的元素中
        beforeSubmit: showRequest,  //提交前的回调函数
        success: showResponse,      //提交后的回调函数
        //url: url,                 //默认是form的action， 如果申明，则会覆盖
        //type: type,               //默认是form的method（get or post），如果申明，则会覆盖
        //dataType: null,           //html(默认), xml, script, json...接受服务端返回的类型
        //clearForm: true,          //成功提交后，清除所有表单元素的值
        //resetForm: true,          //成功提交后，重置所有表单元素的值
        timeout: 3000               //限制请求的时间，当请求大于3秒后，跳出请求
    }

    function showRequest(formData, jqForm, options){
        //formData: 数组对象，提交表单时，Form插件会以Ajax方式自动提交这些数据，格式如：[{name:user,value:val },{name:pwd,value:pwd}]
        //jqForm:   jQuery对象，封装了表单的元素
        //options:  options对象
        var queryString = $.param(formData);   //name=1&address=2
        var formElement = jqForm[0];              //将jqForm转换为DOM对象
        var address = formElement.address.value;  //访问jqForm的DOM元素
        return true;  //只要不返回false，表单都会提交,在这里可以对表单元素进行验证
    };

    function showResponse(responseText, statusText){
        var img = JSON.parse(responseText);
        $("#imgPath").val(img.filePath);
        $("#mini_imgPath").val(img.mini_fileName);
        $("#addpic").attr("src","http://120.77.70.27/"+img.mini_fileName);
    };
    $("#fileUpload").ajaxForm(options);
    $(function(){
        var tmpl = '<li class="weui-uploader__file weui-uploader__file_status" style="background-image:url(#url#)"><div class="weui-uploader__file-content" id="process">0%</div><input style="display: none" value="" id="imgurl" name="ImageList[]"></li>',
            $gallery = $("#gallery"), $galleryImg = $("#galleryImg"),
            $uploaderInput = $("#uploaderInput"),
            $uploaderFiles = $("#uploaderFiles");
        $uploaderInput.on("change", function(e){
            var src, url = window.URL || window.webkitURL || window.mozURL, files = e.target.files;
            var length = $('.weui-uploader__file').length||0;
            for (var i = 0, len = files.length; i < len; ++i) {
                var file = files[i];
                var fd = new FormData();
                imageResizeTool.fileResizetoFile(file,0.5,function(res){
                    //回调中的res是一个压缩后的Blob类型（可以当做File类型看待）文件；
                    console.log(res);
                    var bl = res;
                    fd.append("imagelistToUpload", bl, "file_"+Date.parse(new Date())+".jpg"); // 文件对象
//                    fd.append("username","<?php //echo $openid?>//"); // 文件对象
//                    fd.append("username"); // 文件对象
                    var xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener("progress", uploadProgress, false);
                    xhr.addEventListener("load", uploadComplete, false);
                    xhr.open("POST", "upload.php");
                    xhr.send(fd);
                    //做出你要上传的操作；
                })


                //fd.append("imagelistToUpload", file);
                //var xhr = new XMLHttpRequest();
                //xhr.upload.addEventListener("progress", uploadProgress, false);
                //xhr.addEventListener("load", uploadComplete, false);
                //xhr.open("POST", "upload.php?username=<?//echo $openid?>//");
                //xhr.send(fd);

                if (url) {
                    src = url.createObjectURL(file);
                } else {
                    src = e.target.result;
                }

                $uploaderFiles.append($(tmpl.replace('#url#', src)));
            }
        });
        $uploaderFiles.on("click", "li", function(){
            $galleryImg.attr("style", this.getAttribute("style"));
            $gallery.fadeIn(100);
            $gallery.on("click", function(){
                $gallery.fadeOut(100);
            });
        });

    });

    function uploadProgress(evt) {
        if (evt.lengthComputable) {
            var percentComplete = Math.round(evt.loaded * 100 / evt.total);
            document.getElementById('process').innerHTML = percentComplete.toString() + '%';
        } else {
            document.getElementById('process').innerHTML = '';
        }
    }
    var cnt = 1;
    function uploadComplete(evt) {

        if(evt.target.responseText.indexOf("失败")>=0||evt.target.responseText.indexOf("过大")>=0){
            alert(evt.target.responseText);
        }else{
//            alert("上传成功")
            $("#process").parent().attr("class", "weui-uploader__file");
            $("#process").remove();
            $("#length").html($('.weui-uploader__file').length);
            $("#imgurl").val(evt.target.responseText);
            $("#imgurl").attr("id","Image"+(cnt++));
//            $(".showimg").click(function(){
//                var imgsrc = $(this).attr("src");
//                picBig(imgsrc)
//            });
//
//
//            var imagelistbtn = document.getElementById("addpic");
//            imagelistbtn.onclick = function() {
//                imagelistsub.click();
//            }
        }
//        document.getElementById("image").value=evt.target.responseText;


    }
    function uploadFailed(evt) {
        alert("There was an error attempting to upload the file."+evt);
    }
    function uploadCanceled(evt) {
        alert("The upload has been canceled by the user or the browser dropped the connection.");
    }


    function remo(i) {
        var el = document.getElementById('Image'+i);
        el.parentNode.removeChild(el);
        $("#addpic").show();
    }

</script>


</body>

<!-- Mirrored from www.zi-han.net/theme/hplus/projects.html by HTTrack Website Copier/3.x [XR&CO'2014], Wed, 20 Jan 2016 14:19:44 GMT -->
</html>
