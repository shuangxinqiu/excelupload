<?php
include_once "../common/checkback.php";
$page = isset($_GET['p']) ? trim($_GET['p']) : 1;
$pagesize = 100;
$num = ($page - 1) * $pagesize;
$where = "where 1=1 ";
$limit = "limit $num,$pagesize";
$orderby = "";
if (isset($_GET['orderby'])) {
	$orderby = "order by {$_GET['orderby']} ";
}
if (isset($_GET['desc'])) {
	$orderby .= " desc";
}
//根据路径获取表名
$table = explode('/', $_SERVER['PHP_SELF'])[1];
//获取表字段名
$sql = "select COLUMN_NAME  FROM INFORMATION_SCHEMA.COLUMNS where table_name = '{$table}'";
$stmt = $pdo->query($sql);
$COLUMN_NAME = $stmt->fetchAll(PDO::FETCH_COLUMN);
//获取表字段注释
$sql = "select COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS where table_name = '{$table}'";
$stmt = $pdo->query($sql);
$COLUMN_COMMENT = $stmt->fetchAll(PDO::FETCH_COLUMN);
//根据字段名拼接where
foreach ($COLUMN_NAME as $cn) {
	if ($_REQUEST[$cn]) {
		$where .= "and {$cn} like '%{$_REQUEST[$cn]}%'";
	}
}
//获取列表
$sql = "select * from {$table} {$where} {$orderby} {$limit}";
$stmt = $pdo->query($sql);
if ($stmt->rowCount() > 0) {
	$list = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
	$list = [];
}
//获取数量
$sql = "select * from {$table} {$where}";
$row = $pdo->query($sql)->rowCount();
$to_pages = ceil($row / $pagesize);//总页数
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BYDDPS</title>
    <meta name="keywords" content="">
    <meta name="description" content="">
    <link rel="shortcut icon" href="favicon.ico">
    <link href="/css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="/css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="/css/animate.min.css" rel="stylesheet">
    <link href="/css/style.min862f.css?v=4.1.0" rel="stylesheet">
    <style>
        input {
            width: 38px;
        }

        .gotop {
            position: fixed;
            bottom: 27px;
            right: 15px;
            z-index: 9999;
        }
    </style>
</head>
<body class="gray-bg">
<div class="gotop">
    <a class="open-small-chat" id="gotop">
        <i class="fa fa-arrow-up"></i>
    </a>
</div>
<div class="wrapper wrapper-content animated fadeInUp">
    <div class="row">
        <div class="col-sm-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>所有记录</h5>
                    <div class="ibox-tools" style="top: -5px">
                        <button type="button" class="btn btn-sm btn-danger" id="output">导出</button>
                        <a class="btn btn-success btn-sm" onclick="showexcel()" style="color: white;">导入</a>
                        <a href="add.php" class="btn btn-primary btn-sm">添加</a>
                        <a class="btn btn-primary btn-sm" onclick="deletes()">删除勾选项</a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="row m-b-sm m-t-sm">
                        <div class="col-md-1">
                            <button type="button" id="loading-example-btn" class="btn btn-white btn-sm"
                                    onclick="window.location.reload();"><i
                                        class="fa fa-refresh"></i> 刷新
                            </button>
                        </div>
                        <label class="col-sm-3 control-label"
                               style="padding-right: 0;text-align:  right;height: 32px;line-height: 32px;width: 8%">类别：</label>
                        <div class="col-md-1" style="width: 12%;padding-right: 0;padding-left: 0">
                            <select class="form-control m-b" name="SearchType" id="SearchType" style="height: 32px;">
                                <option <?php if ($UserType == 0) {
									echo "selected";
								} ?>>请选择
                                </option>
								<?php $index = 0;
								$SearchKey = "";
								foreach ($COLUMN_COMMENT as $i) : ?>
                                    <option value='<?php echo $COLUMN_NAME[$index]; ?>'
										<?php if (isset($_REQUEST[$COLUMN_NAME[$index]])) {
											$SearchKey = $_REQUEST[$COLUMN_NAME[$index]];
											echo "selected";
										} ?>
                                    ><?php echo $i; ?></option>
									<?php $index++;endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-11" style="width: 50%;padding-left: 0">
                            <div class="input-group">
                                <input type="text" placeholder="请输入商品名称/品牌关键字" class="input-sm form-control"
                                       id="SearchWord" onkeydown="javascript:if(event.keyCode==13) search();"
                                       style="height: 32px" value="<?php echo $SearchKey; ?>">
                                <span class="input-group-btn">
                                        <button type="button" class="btn btn-sm btn-primary" onclick="search()"
                                                style="height: 32px"> 搜索</button>
                                        <button type="button" class="btn btn-sm btn-default" onclick="search()"
                                                style="height: 32px"> 重置</button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">

                        <table class="table">
                            <tbody>
                            <tr>
<!--                                <input type="checkbox" id="all">-->
								<?php $index = 0;
								foreach ($COLUMN_COMMENT as $i) : ?>
									<?php
									$sql = "select * from config where TableName='{$table}' and ColumnName='{$COLUMN_NAME[$index]}' and UserTypeId='{$_SESSION['backuser']['UserTypeId']}'";
									$stmt = $pdo->query($sql);
									if ($stmt->rowCount() > 0) {
										$cf = $stmt->fetch(PDO::FETCH_ASSOC);
									} else {
										$cf = [];
									}
									?>
									<?php if ($cf['Visible'] == 1): ?>

                                        <td class="project-title" style="<?php if ($i != 'ID') {
											echo "text-align: center";
										} ?>">
											<?php if ($COLUMN_NAME[$index] == 'id'): ?>
                                                <input type="checkbox" id="all">
											<?php endif; ?>
                                            <span onclick="orderby('<?php echo $COLUMN_NAME[$index] ?>','<?php echo $_GET['desc'] ?>')">
											<?php echo $i; ?>
                                            </span>
                                        </td>
									<?php endif; ?>
									<?php $index++;endforeach; ?>

                                <td class="project-actions">
                                    操作
                                </td>
                            </tr>
							<?php foreach ($list as $item) { ?>


                                <tr id="item<?php echo $item['id'] ?>">
									<?php foreach ($COLUMN_NAME as $i) : ?>
										<?php
										$sql = "select * from config where TableName='{$table}' and ColumnName='{$i}' and UserTypeId='{$_SESSION['backuser']['UserTypeId']}'";
										$stmt = $pdo->query($sql);
										if ($stmt->rowCount() > 0) {
											$cf = $stmt->fetch(PDO::FETCH_ASSOC);
										} else {
											$cf = [];
										}
										?>
										<?php if ($cf['Visible'] == 1): ?>

                                            <td class="project-status" style="<?php if ($i != 'id') {
												echo "text-align: center";
											} ?>">
												<?php if ($i == 'id'): ?>
                                                    <input type="checkbox" name="check"
                                                           value="<?php echo $item['id'] ?>">
												<?php endif; ?>
												<?php echo $item[$i]; ?>
                                            </td>
										<?php endif; ?>

									<?php endforeach; ?>
                                    <td class="project-actions" style="width: 10%">
                                        <button type="button" class="btn btn-xs btn-outline btn-primary"
                                                onclick="window.location.href='edit.php?id=<?php echo $item['id'] ?>'">
                                            编辑
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline btn-primary"
                                                onclick="deleteitem(<?php echo $item['id'] ?>)">删除
                                        </button>
                                    </td>
                                </tr>
							<?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="text-align: center">
                        <div class="col-sm-12" style="margin:  0;">
							<?php
							$beginrow = ($page - 1) * $pagesize + 1;
							$endrow = $page * $pagesize;
							if ($endrow > $row) {
								$endrow = $row;
							}
							?>
                            <div class="dataTables_info" id="DataTables_Table_0_info" role="alert"
                                 aria-live="polite" aria-relevant="all">显示 <?php echo $beginrow ?>
                                到 <?php echo $endrow ?> 项，共 <?php echo $row ?> 项
                            </div>

                        </div>
                        <div class="col-sm-7" style="margin: 0;">
                            <div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate"
                                 style="float: right">
                                <ul class="pagination" style="margin: 0">

									<?php
									$beginno = ($page - 3) > 1 ? $page - 3 : 1;
									$endno = ($to_pages - $page) > 3 ? $page + 3 : $to_pages;
									$pre = $page - 1 > 1 ? $page - 1 : 1;
									$next = $page + 1 < $to_pages ? $page + 1 : $to_pages;
									?>

									<?php if (1 != $page): ?>
                                        <li class="paginate_button previous " aria-controls="DataTables_Table_0"
                                            tabindex="0" id="DataTables_Table_0_previous"><a
                                                    href='<?php echo "?status={$Status}&p={$pre}&UserType={$UserType}"; ?>'>上一页</a>
                                        </li>

                                        <li class="paginate_button " aria-controls="DataTables_Table_0" tabindex="0"><a
                                                    href='<?php echo "?status={$Status}&p=1&UserType={$UserType}"; ?>'>首页</a>
                                        </li>
									<?php endif; ?>

									<?php
									for ($i = $beginno; $i <= $endno; $i++):?>
										<?php if ($i == $page): ?>
                                            <li class="paginate_button active" aria-controls="DataTables_Table_0"
                                                tabindex="0"><a
                                                        href='<?php echo "?status={$Status}&p={$i}&UserType={$UserType}"; ?>'><?php echo $i ?></a>
                                            </li>
										<?php else: ?>
                                            <li class="paginate_button " aria-controls="DataTables_Table_0"
                                                tabindex="0"><a
                                                        href='<?php echo "?status={$Status}&p={$i}&UserType={$UserType}"; ?>'><?php echo $i ?></a>
                                            </li>
										<?php endif; ?>
									<?php endfor; ?>

									<?php if ($to_pages != $page): ?>
                                        <li class="paginate_button " aria-controls="DataTables_Table_0" tabindex="0"><a
                                                    href='<?php echo "?status={$Status}&p={$to_pages}&UserType={$UserType}"; ?>'>尾页</a>
                                        </li>

                                        <li class="paginate_button next" aria-controls="DataTables_Table_0" tabindex="0"
                                            id="DataTables_Table_0_next"><a
                                                    href='<?php echo "?status={$Status}&p={$next}&UserType={$UserType}"; ?>'>下一页</a>
                                        </li>
									<?php endif; ?>
                                </ul>

                            </div>

                        </div>
                        <div class="input-group col-sm-2">
							<?php
							$page_banner .= "<form action='" . $_SERVER['PHP_SELF'] . "' method='get' style='width:25%'>";
							$page_banner .= "<input type='hidden'  name='status' value='{$Status}'>";
							$page_banner .= "<input type='hidden'  name='UserType' value='{$UserType}'>";
							$page_banner .= "<input type='number' class='input-sm form-control'  name='p' max='{$to_pages}' min='1'>";
							$page_banner .= "<span class=\"input-group-btn\" style='display: inherit'><button type=\"submit\" class=\"btn btn-sm btn-primary\">跳转</button> </span>";
							$page_banner .= "</form>";
							echo $page_banner;
							?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <img class="bigImg" src="" style="width: 100%;height: 100%"></img>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="input" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="width:600px;">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    &times;
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    导入excel
                </h4>
            </div>
            <div class="modal-body">
                <div>
                    <form action="dataupload.php" id="form" method="post" enctype="multipart/form-data">
                        <label class="layui-form-label">选择excel：</label>
                        <input type="file" id="excel" name="excel" required autocomplete="off" class="layui-input"
                               style="width: 90%"><br>
                        <button type="submit" class="btn btn-primary">
                            提交
                        </button>
                    </form>
                </div>

                <div class="modal-footer" style="border-top-width: 0;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭
                    </button>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="/js/jquery.min.js?v=2.1.4"></script>
<script src="/js/bootstrap.min.js?v=3.3.6"></script>
<script src="/js/content.min.js?v=1.0.0"></script>
<script>
    function openImage(img) {
        $('.bigImg').attr('src', img);
        $('.modal').modal()
    }

    $('#output').click(function () {
        var SearchType = jQuery("#SearchType  option:selected").val();
        var SearchWord = $('#SearchWord').val();
        window.location.href = "./datadownload.php?" + SearchType + "=" + SearchWord;
        console.log("./datadownload.php?" + SearchType + "=" + SearchWord)
    });

    function showexcel() {
        $('#input').modal();
    }

    $(function () {
        //给全选的复选框添加事件
        $("#all").click(function () {
            // this 全选的复选框
            var userids = this.checked;
            //获取name=box的复选框 遍历输出复选框
            $("input[name='check']").each(function () {
                this.checked = userids;
            });
        });
    });

        function deletes() {
            var str = [];
            $("input[name='check']:checkbox").each(function () {
                if ($(this).prop("checked") == true) {
                    str.push($(this).val());
                }
            });
            if (confirm("确定要删除么?")) {
                $.post("action.php?action=deletes",
                    {
                        id: JSON.stringify(str)
                    },
                    function (result) {
                        if (result) {
                            alert('删除成功!');
                            $("input[name='check']:checkbox").each(function () {
                                if ($(this).prop("checked") == true) {
                                    $(this).parents('tr').remove();
                                }
                            });
                        } else {
                            alert('删除失败!')
                        }
                    });
            }
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

        function deleteitem(id) {
            if (confirm("确定要删除么?")) {
                $.post("action.php?action=delete",
                    {
                        id: id
                    },
                    function (result) {
                        if (result) {
                            alert('删除成功!');
                            $("#item" + id).remove();
                        } else {
                            alert('删除失败!')
                        }
                    });
            }
        }

        function search() {
            var SearchType = jQuery("#SearchType  option:selected").val();
            var SearchWord = $('#SearchWord').val();
            window.location.href = "list.php?" + SearchType + "=" + SearchWord;
        }


        function orderby(orderby, desc) {
//        alert(orderby);
            var SearchType = jQuery("#SearchType  option:selected").val();
            var SearchWord = $('#SearchWord').val();
            if (desc)
                window.location.href = "list.php?" + SearchType + "=" + SearchWord + "&orderby=" + orderby;
            else
                window.location.href = "list.php?" + SearchType + "=" + SearchWord + "&orderby=" + orderby + "&desc=1";

        }

        $('#gotop').click(function () {
            $('html,body').animate({scrollTop: 0}, 500);
        });
</script>
</body>
</html>
