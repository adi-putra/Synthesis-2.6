<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . '/synthesis/session.php');
// include "session.php";

$timefrom = $_GET['timefrom'] ?? strtotime("today");
$timetill = $_GET['timetill'] ?? time();
//display time format
$diff = $timetill - $timefrom;
if ($diff == 3600) {
  $status = "Last 1 hour";
}
else if ($diff < 86400) {
  $status = "Today";
}
else if ($diff == 86400) {
  $status = "Last 1 day";
}
else if ($diff == 172800) {
  $status = "Last 2 days";
}
else if ($diff == 604800) {
  $status = "Last 7 days";
}
else if ($diff == 2592000) {
  $status = "Last 30 days";
}

$mapid = $_GET["mapid"];

$params = array(
    "output" => "extend",
    "sysmapids" => $mapid,
    "selectSelements" => "extend",
    "selectLinks" => "extend", 
    "selectShapes" => "extend",
    "selectLines" => "extend"
);
$result = $zbx->call('map.get', $params);

foreach ($result as $map) {

    $map_width = $map["width"] + 100;
    $map_height = $map["height"] + 100;

    $elem_json_map[] = array(
        "width" => $map_width,
        "height" => $map_height
    );

    foreach ($map["selements"] as $elem) {

        $params = array(
            "output" => "extend",
            "imageids" => $elem["iconid_off"],
            "select_image" => true
        );
        $result = $zbx->call('image.get', $params);
        foreach ($result as $image) {
            $image_url = "data:image/png;base64," . $image["image"];
            $image_name = $image["name"];
        }

        $hostid = "";
        $sysmapid = "";
        $triggerid = "";
        $groupid = "";
        $problemCount = "";

        if (empty($elem["elements"])) {
            $hostid = "";
            $sysmapid = "";
            $triggerid = "";
            $groupid = "";
            $problemCount = "";
            $problemColor = "";
            $visible_name = $elem["label"];
        } else {

            foreach ($elem["elements"] as $em) {

                if ($elem["elementtype"] == 0) {
                    $hostid = $em["hostid"];
                    $params = array(
                        "output" => "extend",
                        "hostids" => $hostid,
                        "time_from" => $timefrom,
                        "time_till" => $timetill,
                        "countOutput" => true
                    );
                    $result = $zbx->call('problem.get', $params);
                    $problemCount = $result;

                    $params = array(
                        "hostids" => $hostid,
                        "output" => ["name"]
                    );
                    $result = $zbx->call('host.get', $params);
                    foreach ($result as $host) {
                        $visible_name = $host["name"];
                    }
                }

                if ($elem["elementtype"] == 1) {
                    $sysmapid =  $em["sysmapid"];
                    $problemCount = "";

                    $params = array(
                        "sysmapids" => $sysmapid,
                        "output" => ["name"]
                    );
                    $result = $zbx->call('map.get', $params);
                    foreach ($result as $sysmap) {
                        $visible_name = $sysmap["name"];
                    }
                }

                if ($elem["elementtype"] == 2) {
                    $triggerid = $em["triggerid"];
                    $problemCount = "";
                    $params = array(
                        "triggerids" => $triggerid,
                        "output" => ["description"]
                    );
                    $result = $zbx->call('trigger.get', $params);
                    foreach ($result as $trigger) {
                        $visible_name = $trigger["description"];
                        $triggername = $trigger["description"];
                    }
                }

                if ($elem["elementtype"] == 3) {
                    $groupid = $em["groupid"];
                    $params = array(
                        "output" => "extend",
                        "groupids" => $groupid,
                        "time_from" => $timefrom,
                        "time_till" => $timetill,
                        "countOutput" => true
                    );
                    $result = $zbx->call('problem.get', $params);
                    $problemCount = $result . " ";
                    // echo "Host ID: $hostid\n";
                    // echo "Problem Count: $problemCount\n\n";

                    $params = array(
                        "groupids" => $groupid,
                        "output" => ["name"]
                    );
                    $result = $zbx->call('hostgroup.get', $params);
                    foreach ($result as $hostgp) {
                        $visible_name = $hostgp["name"];
                    }
                }

                if ($problemCount > 0) {
                    $problemCount = $problemCount . " problems";
                    $problemColor = "red";
                } else {
                    $problemColor = "green";
                    $problemCount = "";
                }
            }
        }

        $elem_json_selement[] = array(
            "img" => array(
                "type" => "image",
                "selementid" => $elem["selementid"],
                "selementtype" => $elem["elementtype"],
                "name" => $image_name,
                "labelLocation" => $elem["label_location"],
                "visibleName" => $visible_name,
                "src" => $image_url,
                "originX" => 'left',
                "originY" => 'top',
                "left" => $elem["x"],
                "top" => $elem["y"],
                "tleft" => $elem["x"],
                "ttop" => $elem["y"],
                "width" => $elem["width"],
                "height" => $elem["height"],
                "hostid" =>  $hostid,
                "sysmapid" => $sysmapid,
                "triggerid" => $triggerid,
                "groupid" => $groupid,
                "pcount" => $problemCount,
                "pcolor" => $problemColor
            ),

            "txt" => array(
                "type" => "text",
                "name" => $elem["label"],
                "fontSize" => 12,
                "fill" => "#000000",
                "left" => $elem["x"],
                "top" => $elem["y"],
            )
        );
    }

    foreach ($map["links"] as $link) {
        $linkid = $link["linkid"];

        foreach ($link['linktriggers'] as $linktrig) {
            $linktrig_id = $linktrig['triggerid'];

            $params = array(
                "output" => ["description", "value"],
                "triggerids" => $linktrig_id
            );
            $result = $zbx->call('trigger.get', $params);
            foreach ($result as $trigger) {
                $trig_name = $trigger["description"];
                $trig_value = $trigger["value"];
            }
        }

        $elem_json_link[] = array(
            "links" => array(
                "type" => "line",
                "linkid" => $linkid,
                "sysmapid" => $link["sysmapid"],
                "selementid1" => $link["selementid1"],
                "selementid2" => $link["selementid2"],
                "drawtype" => $link["drawtype"],
                "color" => $link["color"],
                "label" => $link["label"]
            ),

            "linktriggers" => array(
                "linktriggersid" => $linktrig["linktriggerid"],
                "linkid" => $linktrig["linkid"],
                "triggerid" => $linktrig["triggerid"],
                "drawtype" => $linktrig["drawtype"],
                "color" => $linktrig["color"],
                "triggername" => $trig_name,
                "triggervalue" => $trig_value
            )
        );
    }

    foreach ($map["shapes"] as $shape) {
        $shapeid = $shape['sysmap_shapeid'];

        if (empty($shape)) {
            $shapeX = "";
            $shapeY = "";
            $shapeW = "";
            $shapeH = "";
            $shape_text = "";
            $shape_font = "";
            $shape_fontSize = "";
            $shape_fontColor = "";
            $shape_textHalign = "";
            $shape_borderType = "";
            $shape_borderW = "";
            $shape_borderColor = "";
            $shape_backgroundColor = "";
            $shape_zIndex = "";
        } else {
            $shape_type = $shape['type'];
            $shapeX = $shape["x"];
            $shapeY = $shape["y"];
            $shapeW = $shape["width"];
            $shapeH = $shape["height"];
            $shape_text = $shape["text"];
            $shape_font = $shape["font"];
            $shape_fontSize = $shape["font_size"];
            $shape_fontColor = $shape["font_color"];
            $shape_textHalign = $shape["text_halign"];
            $shape_textValign = $shape["text_valign"];
            $shape_borderType = $shape["border_type"];
            $shape_borderW = $shape["border_width"];
            $shape_borderColor = $shape["border_color"];
            $shape_zIndex = $shape["zindex"];

            if ($shape["background_color"] != "") {
                $shape_backgroundColor = '#' . $shape["background_color"];
            } else {
                $shape_backgroundColor = 'rgba(0, 0, 0, 0)';
            }

            if ($shape["border_color"] == "") {
                $shape_borderColor = 'rgba(0, 0, 0, 0)';
                $shape_borderW = 0;
            }
        }

        $elem_json_shape[] = array(
            "type" => $shape_type,
            "left" => $shapeX,
            "top" => $shapeY,
            "width" => $shapeW,
            "height" => $shapeH,
            "text" => $shape_text,
            "font" => $shape_font,
            "font_size" => $shape_fontSize,
            "font_color" => $shape_fontColor,
            "text_halign" => $shape_textHalign,
            "text_valign" => $shape_textValign,
            "border_type" => $shape_borderType,
            "border_width" => $shape_borderW,
            "border_color" => '#' . $shape_borderColor,
            "background_color" => $shape_backgroundColor,
            "zindex" => $shape_zIndex
        );
    }

    foreach ($map["lines"] as $line) {
        $sysmap_shapeid = $line['sysmap_shapeid'];


        $elem_json_line[] = array(
            "sysmap_shapeid" => $sysmap_shapeid,
            "zindex" => $line['zindex'],
            "x1" => $line['x1'],
            "y1" => $line['y1'],
            "x2" => $line['x2'],
            "y2" => $line['y2'],
            "line_type" => $line['line_type'],
            "line_width" => $line['line_width'],
            "line_color" => $line['line_color']
        );
    }
}

$map_json = array(
    "obj_map" => $elem_json_map,
    "obj_selement" => $elem_json_selement,
    "obj_link" => $elem_json_link,
    "obj_shape"  => $elem_json_shape,
    "obj_line" => $elem_json_line
);
$map_json = json_encode($map_json);

?>
<html>

<head>
</head>
<style>
    /* Define the class to be used
  as the wrapper of the Canvas */

    .sysmap-scroll-container {
        overflow-x: auto;
        overflow-y: hidden;
        position: relative;
        width: calc(100% - 20px);
        border: 10px solid #2b2b2b;
        background: #2b2b2b;
        display: block;
        margin-top: 4px;
    }

    .MapCanvas {
        background-color: #2b2b2b;
    }
</style>

<body>
    <caption><i>Updated since: <?php echo date("d/m/y h:i A", time()); ?></i></caption>

    <div class="sysmap-scroll-container">
        <canvas id="canvas"></canvas>
    </div>

</body>

</html>
<script>
    var map_json = <?php echo $map_json; ?>;
    var map = map_json.obj_map;
    var selements = map_json.obj_selement;
    var links = map_json.obj_link;
    var shapes = map_json.obj_shape;
    var lines = map_json.obj_line;

    // console.log(lines);
    // console.log(JSON.stringify(map_json));
    var canvas = new fabric.Canvas('canvas', {
        selection: false,
        evented: false,
        containerClass: "MapCanvas",
    });

    var grid = 50;
    var unitScale = 10;
    var mapHeight = <?php echo $map_height; ?>;
    var mapWidth = <?php echo $map_width; ?>;

    canvas.setHeight(mapHeight);
    canvas.setWidth(mapWidth);
    // console.log(links);

    selements.forEach(function(obj) {
        fabric.Image.fromURL(obj.img.src, function(imgObject) {

            imgObject.set({
                originX: obj.img.originX,
                originY: obj.img.originY,
                left: parseInt(obj.img.left),
                top: parseInt(obj.img.top),
                scaleX: 1,
                scaleY: 1,
                evented: false,
                selectable: false,
                shadow: {
                    color: obj.img.pcolor,
                    blur: 20
                }
            });

            var txtName = new fabric.Text(obj.img.visibleName, {
                fontSize: 12,
                fontFamily: 'Arial',
                fill: 'white',
                evented: false,
                selectable: false,
            });

            var txtProblem = new fabric.Text(obj.img.pcount, {
                fill: obj.img.pcolor,
                left: parseInt(obj.txt.left),
                top: parseInt(obj.txt.top) + 114,
                fontSize: obj.txt.fontSize,
                fontFamily: 'Arial',
                evented: false,
                selectable: false
            });

            var elemTxt_top = parseInt(obj.img.top) - 20;
            var elemTxt_middle = parseInt(obj.img.top) + (parseInt(imgObject.height) - parseInt(txtName.height)) / 2;
            var elemTxt_bottom = parseInt(obj.img.top) + parseInt(imgObject.height) + 20;

            var elemTxt_left = parseInt(obj.img.left) - parseInt(imgObject.width) / 2;
            var elemTxt_center = parseInt(obj.img.left) + (parseInt(imgObject.width) - parseInt(txtName.width)) / 2;
            var elemTxt_right = parseInt(obj.img.left) + parseInt(imgObject.width);
            var elemTxt_problem_center = parseInt(txtName.left) + (parseInt(txtName.width) - parseInt(txtProblem.width)) / 2

            //Default or Bottom
            if (obj.img.labelLocation == 0 || obj.img.labelLocation == -1) {
                txtName.set({
                    left: elemTxt_center,
                    top: elemTxt_bottom
                });
                txtProblem.set({
                    top: elemTxt_bottom + 20,
                    // calculate the center point for horizontally aligning the text by taking the left position of the image object,
                    // adding half of the image object's width, and subtracting half of the width of the text object.
                    left: parseInt(txtName.left) + (parseInt(txtName.width) - parseInt(txtProblem.width)) / 2
                });
            }

            //Top
            if (obj.img.labelLocation == 3) {
                txtName.set({
                    left: elemTxt_center,
                    top: elemTxt_top
                });
                txtProblem.set({
                    left: elemTxt_center,
                    top: elemTxt_top - 20
                });
            }

            //Right
            if (obj.img.labelLocation == 2) {
                txtName.set({
                    left: elemTxt_right,
                    top: elemTxt_middle
                });
                txtProblem.set({
                    left: elemTxt_right,
                    top: elemTxt_middle + 20
                });
            }

            //left
            if (obj.img.labelLocation == 1) {
                txtName.set({
                    left: elemTxt_left,
                    top: elemTxt_middle
                });
                txtProblem.set({
                    left: elemTxt_left,
                    top: elemTxt_middle + 20
                });
            }

            var txtBg = new fabric.Rect({
                fill: 'rgba(43, 43, 43, 0.5)',
                width: txtName.width + 30,
                height: txtName.height + 40,
                left: txtName.left - 10,
                top: txtName.top - 10,
                opacity: 1
            });

            if (obj.img.pcount == "" || obj.img.pcount == null) {
                txtBg.set({
                    height: txtName.height + 20
                });
            }

            var txtGroup = new fabric.Group([txtBg, txtName, txtProblem], {});
            var ImgGroup = new fabric.Group([imgObject, txtGroup], {
                evented: true,
                selectable: false,
                hasControls: false,
                hasBorders: false,
                hoverCursor: "arrow",
                opacity: 0
            });

            canvas.add(txtGroup, ImgGroup);

            if (obj.img.selementtype == 0) {

                ImgGroup.set({
                    hoverCursor: "pointer",
                    zIndex: -1
                });


                ImgGroup.on("mousedown", function(CHost) {
                    window.open(
                        "hostdetails.php?hostid=" + obj.img.hostid,
                        '_blank' // <- This is what makes it open in a new window.
                    );
                });
            }

            // if (obj.img.selementtype == 1) {
            //     ImgGroup.on("mousedown", function(CSysmap) {
            //         window.location.href = "http://172.16.210.117/synconfig/zabbix.php?action=map.view&sysmapid=" + obj.img.sysmapid;
            //     });
            // }
            // if (obj.img.selementtype == 2) {
            //     ImgGroup.on("mousedown", function(CTrigger) {
            //         window.location.href = "http://172.16.210.117/synconfig/zabbix.php?show=1&name=&inventory%5B0%5D%5Bfield%5D=type&inventory%5B0%5D%5Bvalue%5D=&evaltype=0&tags%5B0%5D%5Btag%5D=&tags%5B0%5D%5Boperator%5D=0&tags%5B0%5D%5Bvalue%5D=&show_tags=3&tag_name_format=0&tag_priority=&show_opdata=0&show_timeline=1&filter_name=&filter_show_counter=0&filter_custom_time=0&sort=clock&sortorder=DESC&age_state=0&show_suppressed=0&unacknowledged=0&compact_view=0&details=0&highlight_row=0&action=problem.view&triggerids%5B%5D=" + obj.img.triggerid;
            //     });
            // }
            // if (obj.img.selementtype == 3) {

            //     ImgGroup.set({
            //         hoverCursor: "pointer"
            //     });

            //     ImgGroup.on("mousedown", function(CGroup) {
            //         window.location.href = "http://172.16.210.117/synconfig/zabbix.php?show=1&name=&inventory%5B0%5D%5Bfield%5D=type&inventory%5B0%5D%5Bvalue%5D=&evaltype=0&tags%5B0%5D%5Btag%5D=&tags%5B0%5D%5Boperator%5D=0&tags%5B0%5D%5Bvalue%5D=&show_tags=3&tag_name_format=0&tag_priority=&show_opdata=0&show_timeline=1&filter_name=&filter_show_counter=0&filter_custom_time=0&sort=clock&sortorder=DESC&age_state=0&show_suppressed=0&unacknowledged=0&compact_view=0&details=0&highlight_row=0&action=problem.view&groupids%5B%5D=" + obj.img.groupid;
            //     });
            // }

            ImgGroup.animate('opacity', 1, {
                duration: 2000, // in milliseconds
                onChange: canvas.renderAll.bind(canvas),
                easing: fabric.util.ease.easeInOutCubic
            });


        });
    });

    function createShape(objs, canvas) {

        if (objs.type == 0) {
            var rect = new fabric.Rect({
                originX: 'left',
                originY: 'top',
                left: parseInt(objs.left),
                top: parseInt(objs.top),
                width: parseInt(objs.width),
                height: parseInt(objs.height),
                fill: objs.background_color,
                stroke: objs.border_color,
                strokeWidth: parseInt(objs.border_width),
                zIndex: parseInt(objs.zindex)
            });

            var text = new fabric.Text(objs.text, {
                fill: '#' + objs.font_color,
                left: 0,
                top: 0,
                fontSize: objs.font_size,
                fontFamily: 'Arial',
                evented: false,
                selectable: false,
                zIndex: parseInt(objs.zindex)
            });

            var textX_left = rect.left + 5;
            var textX_center = rect.left + (rect.width - text.width) / 2;
            var textX_right = (rect.left - 5) + rect.width - text.width;

            var textY_top = rect.top + 5;
            var textY_middle = rect.top + (rect.height - text.height) / 2;
            var textY_bottom = rect.top - 5 + rect.height - text.height;

            if (objs.text_halign == 0) {
                text.set({
                    left: textX_center
                });
            }
            if (objs.text_halign == 1) {
                text.set({
                    left: textX_left
                });
            }
            if (objs.text_halign == 2) {
                text.set({
                    left: textX_right
                });
            }
            if (objs.text_valign == 0) {
                text.set({
                    top: textY_middle
                });
            }
            if (objs.text_valign == 1) {
                text.set({
                    top: textY_top
                });
            }
            if (objs.text_valign == 2) {
                text.set({
                    top: textY_bottom
                })
            }

            var ShapeGroup = new fabric.Group([rect, text], {
                evented: false,
                selectable: false,
                hasControls: false,
                hasBorders: false,
                opacity: 0
            });
        }

        if (objs.type == 1) {

            var radiusCal = Math.min(objs.width, ) / 2;
            var scaleX = objs.width / (radiusCal * 2);
            var scaleY = objs.height / (radiusCal * 2);
            var rx = parseInt(objs.width) / 2;
            var ry = parseInt(objs.height) / 2;

            var circle = new fabric.Ellipse({
                originX: 'left',
                originY: 'top',
                left: parseInt(objs.left),
                top: parseInt(objs.top),
                rx: rx,
                ry: ry,
                fill: objs.background_color,
                stroke: '#' + objs.border_color,
                strokeWidth: parseInt(objs.border_width),

            });

            var text = new fabric.Text(objs.text, {
                fill: 'white',
                left: circle.left,
                top: circle.top,
                fontSize: objs.font_size,
                fontFamily: 'Arial',
                evented: false,
                selectable: false,
            });

            var textX_left = circle.left + 20;
            var textX_center = circle.left + (circle.width - text.width) / 2;
            var textX_right = (circle.left - 20) + circle.width - text.width;

            var textY_top = circle.top + 20;
            var textY_middle = circle.top - 45 + (circle.height - text.height) / 2;
            var textY_bottom = circle.top - 20 + circle.height - text.height;

            if (objs.text_halign == 0) {
                text.set({
                    left: textX_center
                });
            }
            if (objs.text_halign == 1) {
                text.set({
                    left: textX_left
                });
            }
            if (objs.text_halign == 2) {
                text.set({
                    left: textX_right
                });
            }
            if (objs.text_valign == 0) {
                text.set({
                    top: textY_middle
                });
            }
            if (objs.text_valign == 1) {
                text.set({
                    top: textY_top
                });
            }
            if (objs.text_valign == 2) {
                text.set({
                    top: textY_bottom
                })
            }

            var ShapeGroup = new fabric.Group([circle, text], {
                evented: false,
                selectable: false,
                hasControls: false,
                hasBorders: false,
                opacity: 0,
                zIndex: parseInt(objs.zindex)
            });
        }
        canvas.add(ShapeGroup);

        // bring to front or send to back based on zIndex
        canvas.getObjects().forEach(function(obj) {
            var zIndexValue = obj.zIndex;

            if (obj !== ShapeGroup ) {
                if (zIndexValue < obj.zIndex) {
                    ShapeGroup.bringToFront();
                } else 
                if (zIndexValue > obj.zIndex){
                    ShapeGroup.sendToBack();
                }
                canvas.renderAll(); // moved inside the forEach loop
            }
        });

        ShapeGroup.animate('opacity', 1, {
            duration: 2000,
            onChange: function() {
                canvas.renderAll(); // called on each animation frame
            },
            easing: fabric.util.ease.easeInOutCubic
        });

        // console.log(ShapeGroup.zIndex, ShapeGroup.type);
        return ShapeGroup;

    }

    if (shapes != null) {
        shapes.forEach(objs => {
            createShape(objs, canvas);
        });
    }

    // console.log(lines)
    function createLine(obj_line, canvas) {

        var x1 = parseInt(obj_line.x1);
        var y1 = parseInt(obj_line.y1);
        var x2 = parseInt(obj_line.x2);
        var y2 = parseInt(obj_line.y2);

        // console.log(x1, y1, x2, y2);
        var line = new fabric.Line([x1, y1, x2, y2], {
            stroke: '#' + obj_line.line_color,
            strokeWidth: parseInt(obj_line.line_width),
            evented: true,
            hasControls: true,
            hasBorders: true,
            opacity: 0,
            zIndex: parseInt(obj_line.zindex)
        });

        if (obj_line.line_type == 2) {
            line.set({
                strokeDashArray: [1, 33],
            });
        }

        if (obj_line.line_type == 3) {
            line.set({
                strokeDashArray: [34, 38]
            });
        }
        console.log(obj_line.line_type);
        line.animate('opacity', 1, {
            onChange: canvas.renderAll.bind(canvas),
            duration: 1000,
            easing: fabric.util.ease.easeInOutQuad
        });
        canvas.add(line);
        // bring to front or send to back based on zIndex
        canvas.getObjects().forEach(function(obj) {
            var zIndexValue = obj.zIndex;

            if (obj !== line) {
                if (zIndexValue < obj.zIndex) {
                    line.bringToFront();
                } else if (zIndexValue > obj.zIndex){
                    ShapeGroup.sendToBack();
                }
                canvas.renderAll(); // moved inside the forEach loop
            }
        });

        console.log(line.zIndex, line.type, line.stroke);
    }

    if (lines != null) {
        lines.forEach(obj_line => {
            createLine(obj_line, canvas);
        });
    }

    function createLinks(objl, s1, s2, canvas) {

        var x1 = parseInt(s1.left) + 40;
        var y1 = parseInt(s1.top) + 40;
        var x2 = parseInt(s2.left) + 40;
        var y2 = parseInt(s2.top) + 40;

        var x = (x1 + x2 - 100) / 2;
        var y = (y1 + y2 - 10) / 2;

        var xx = (x1 + x2 - 30) / 2;
        var yy = (y1 + y2 - 30) / 2;

        var linked = new fabric.Line([x1, y1, x2, y2], {
            stroke: '#' + objl.links.color,
            strokeWidth: 1,
            evented: false,
            selectable: false,
            hasControls: false,
            hasBorders: false,
            opacity: 0
        });

        if (objl.links.drawtype == 2) {
            linked.set({
                strokeWidth: 3
            });
        }

        if (objl.links.drawtype == 3) {
            linked.set({
                strokeDashArray: [1, 2]
            });
        }

        if (objl.links.drawtype == 4) {
            linked.set({
                strokeDashArray: [3, 5]
            });
        }

        if (objl.linktriggers != "") {
            if (objl.linktriggers.triggervalue == 1) {
                linked.set({
                    stroke: '#' + objl.linktriggers.color
                });
            }
        }
        linked.animate('opacity', 1, {
            onChange: canvas.renderAll.bind(canvas),
            duration: 1000,
            easing: fabric.util.ease.easeInOutQuad
        });
        // console.log(objl.linktriggers.triggername);
        canvas.add(linked);

        if (objl.links.label != "") {
            var text = new fabric.Text(objl.links.label, {
                left: xx,
                top: yy,
                fill: '#' + objl.links.color,
                backgroundColor: 'rgba(43, 43, 43, 1)',
                fontSize: 12,
                fontFamily: 'Arial',
                evented: false,
                selectable: false,
                opacity: 0
            });
            canvas.add(text);
            text.animate('opacity', 1, {
                onChange: canvas.renderAll.bind(canvas),
                duration: 1000,
                easing: fabric.util.ease.easeInOutQuad
            });
        }

        if (objl.linktriggers.triggervalue == 1) {
            var text = new fabric.Text(objl.linktriggers.triggername, {
                left: x,
                top: y,
                fill: '#' + objl.linktriggers.color,
                backgroundColor: 'rgba(43, 43, 43, 1)',
                fontSize: 12,
                fontFamily: 'Arial',
                evented: false,
                selectable: false,
                opacity: 0
            });
            if (objl.linktriggers.drawtype == 2) {
                linked.set({
                    strokeWidth: 3
                });
            }
            if (objl.linktriggers.drawtype == 3) {
                linked.set({
                    strokeDashArray: [1, 2]
                });
            }
            if (objl.linktriggers.drawtype == 4) {
                linked.set({
                    strokeDashArray: [3, 5]
                });
            }
            text.animate('opacity', 1, {
                onChange: canvas.renderAll.bind(canvas),
                duration: 1000,
                easing: fabric.util.ease.easeInOutQuad
            });
            canvas.add(text);
        }
    }

    linkq: {
        if (links != null) {
            for (var i = 0; i < links.length; i++) {
                var objl = links[i];
                var sid1 = objl.links.selementid1;
                var sid2 = objl.links.selementid2;
                var s1, s2;

                // console.log(objl);
                for (var j = 0; j < selements.length; j++) {
                    var obj = selements[j];
                    var img = obj.img;

                    if (sid1 === img.selementid) {
                        s1 = img;
                    }
                    if (sid2 === img.selementid) {
                        s2 = img;
                    }
                }

                if (s1 && s2) {
                    createLinks(objl, s1, s2, canvas);
                }

            }
        } else {
            break linkq;
        }
    }

    // var lastdatetime_x = parseInt(mapWidth) / 10;
    // var lastdatetime_y = parseInt(mapHeight) - 50;

    // var text = new fabric.Text('', {
    //     left: lastdatetime_x,
    //     top: lastdatetime_y,
    //     fontFamily: 'Arial',
    //     fontSize: 12,
    //     fill: 'white',
    // });

    // canvas.add(text);

    // setInterval(() => {
    //     var date = new Date();
    //     text.set('text', date.toString());
    //     canvas.renderAll();
    // }, 1000);
</script>