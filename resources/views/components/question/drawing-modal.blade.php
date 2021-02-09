<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>

    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link href="/drawing/buttons.css" rel="stylesheet" type="text/css" />
    <link href="/drawing/spacing.css" rel="stylesheet" type="text/css" />
    <style>
        body {
            font-family: Myriad Pro, Arial;
        }
    </style>

    <title>HTML5 viewport</title>
</head>
<body>

<header>
    <div>


        <!--<a id="btn-undo" title="Ongedaan maken" class="btn highlight small mr2 pull-left"><span class="fa fa-mail-reply"></span></a>
            <a id="btn-redo" title="Stap vooruit" class="btn highlight small mr2 pull-left"><span class="fa fa-mail-forward"></span></a>-->
        <a id="btn-tool-freeform" title="Tekenen" class="btn highlight small mr2 pull-left">
            <span class="fa fa-paint-brush"></span>
        </a>
        <a id="btn-tool-line" title="Lijn" class="btn highlight small mr2 pull-left">
            <span class="fa fa-minus"></span>
        </a>
        <a id="btn-tool-arrow" title="Pijl" class="btn highlight small mr2 pull-left">
            <span class="fa fa-long-arrow-right"></span>
        </a>
        <a id="btn-tool-shape-circle" title="Cirkel" class="btn highlight small mr2 pull-left">
            <span class="fa fa-circle-thin"></span>
        </a>
        <a id="btn-tool-shape-rectangle" title="Vierkant" class="btn highlight small mr2 pull-left">
            <span class="fa fa-square-o"></span>
        </a>

        <span id="btn-export"></span>
        <a  x-on:click="
                (function() {
                    $wire.set('answer', eppi.getActiveImageBase64Encoded());
                })()"
            class="btn highlight small ml5 pull-right" style="cursor: pointer;">
            <span class="fa fa-check"></span> Opslaan
        </a>
        <a class="btn grey small ml5 pull-right" style="cursor:pointer;" @click="opened = false;">
            <span class="fa fa-remove"></span> Sluiten
        </a>

        <a id="btn-color-blue" class="btn small p-0 w-7 h-7 mr2 pull-right colorBtn"
           style="background: blue; opacity: .3;"></a>
        <a id="btn-color-red" class="btn small p-0 w-7 h-7 mr2 pull-right colorBtn"
           style="background: red; opacity: .3;"></a>
        <a id="btn-color-green" class="btn small p-0 w-7 h-7 mr2 pull-right colorBtn"
           style="background: green; opacity: .3;"></a>
        <a id="btn-color-black" class="btn p-0 w-7 h-7 small mr2 ml10 pull-right colorBtn"
           style="background: black;"></a>

        <a id="btn-thick-1" class="btn small mr2  pull-right thickBtn highlight"
           title="lijndikte 1">
            <img src="/img/ico/line1.png"/>
        </a>
        <a id="btn-thick-2" class="btn small mr2  pull-right thickBtn highlight"
           title="lijndikte 2">
            <img src="/img/ico/line2.png"/>
        </a>
        <a id="btn-thick-3" class="btn small mr2 ml10 pull-right thickBtn highlight"
           title="lijndikte 3">
            <img src="/img/ico/line3.png"/>
        </a>
    </div>
</header>

<div id="canvas-holder" class="v-center__wrapper rounded-10"
     style="border:1px solid gray; width: 970px; float:left; margin-top: 10px;">

</div>

<div id="layers-holder" class="rounded-10"
     style="border: 1px solid gray; width: 200px; float:left; margin-left: 10px; height: 481px; overflow: auto; margin-top: 10px;">

</div>
<div class="input-group w-full mt-4">

<textarea id="additional_text" wire:model="additionalText"
          class="form-input"
          placeholder="Begeleidende tekst"></textarea>
</div>

<script type="text/javascript">
</script>

<!-- Vendors -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="/drawing/filesaver.min.js"></script>
<script src="/drawing/canvas-toblob.js"></script>

<script src="/drawing/paint.js"></script>
<script src="/drawing/loadPaint.js"></script>
{{--<script src="/drawing/test_take.js?20201014130801"></script>--}}

</body>
</html>