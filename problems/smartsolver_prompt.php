<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/synthesis/session.php");

$this_filepath = str_replace("var/www/html/", "", $_SERVER['DOCUMENT_ROOT'] . "/synthesis/dashboard/problems/request.php"); 

$eventid = $_GET["eventid"];

$params = array(
    'output' => "extend",
    'eventids' => $eventid,
);
$result = $zbx->call('problem.get', $params);
foreach ($result as $prob) {
    $prob_name =  $prob["name"];
}

$fixedprompt = "Act as expert problem Solver, I want in bullet point format, make sure point are space between.
                Explain what is this problem, 
                what are the root cause and, 
                what are the possible way to solve it with up to date URL for reference 
                if the url not valid or not found related to the prompt or problems,  dont show it.";
?>

<!DOCTYPE html>
<html lang="en">

<body class="skin-blue">
    <div class="modal-body" id="ackformModal" style="overflow-y: auto; height:400px;">
        <h3 align="center"><?php echo $prob_name; ?></h3>
        <div class="row">
            <!-- <div class="col-xs-12">
                <input id="ai_prompt" type="hidden" placeholder="Ask me something." value="<?php echo $prob_name; ?>">
            </div> -->
            <div class="col-xs-12">
                <textarea class="form-control" id="ai_textarea" ></textarea>
            </div>
        </div>
    </div>
</body>

</html>

<script>
/**
 * Copyright (C) 2016 Maxime Petazzoni <maxime.petazzoni@bulix.org>.
 * All rights reserved.
 */

// $(function() {
//     autosize($('#ai_textarea'));

//     $('#ai_textarea').each(function(){
//         autosize(this);
//     }).on('autosize:resized', function(){
//         console.log('textarea height updated');
//     });
// });

var SSE = function(url, options) {
    if (!(this instanceof SSE)) {
        return new SSE(url, options);
    }

    this.INITIALIZING = -1;
    this.CONNECTING = 0;
    this.OPEN = 1;
    this.CLOSED = 2;

    this.url = url;

    options = options || {};
    this.headers = options.headers || {};
    this.payload = options.payload !== undefined ? options.payload : "";
    this.method = options.method || (this.payload && "POST") || "GET";
    this.withCredentials = !!options.withCredentials;

    this.FIELD_SEPARATOR = ":";
    this.listeners = {};

    this.xhr = null;
    this.readyState = this.INITIALIZING;
    this.progress = 0;
    this.chunk = "";

    this.addEventListener = function(type, listener) {
        if (this.listeners[type] === undefined) {
            this.listeners[type] = [];
        }

        if (this.listeners[type].indexOf(listener) === -1) {
            this.listeners[type].push(listener);
        }
    };

    this.removeEventListener = function(type, listener) {
        if (this.listeners[type] === undefined) {
            return;
        }

        var filtered = [];
        this.listeners[type].forEach(function(element) {
            if (element !== listener) {
                filtered.push(element);
            }
        });
        if (filtered.length === 0) {
            delete this.listeners[type];
        } else {
            this.listeners[type] = filtered;
        }
    };
     
    this.dispatchEvent = function(e) {
        if (!e) {
            return true;
        }

        e.source = this;

        var onHandler = "on" + e.type;
        if (this.hasOwnProperty(onHandler)) {
            this[onHandler].call(this, e);
            if (e.defaultPrevented) {
                return false;
            }
        }

        if (this.listeners[e.type]) {
            return this.listeners[e.type].every(function(callback) {
                callback(e);
                return !e.defaultPrevented;
            });
        }

        return true;
    };

    this._setReadyState = function(state) {
        var event = new CustomEvent("readystatechange");
        event.readyState = state;
        this.readyState = state;
        this.dispatchEvent(event);
    };

    this._onStreamFailure = function(e) {
        var event = new CustomEvent("error");
        event.data = e.currentTarget.response;
        this.dispatchEvent(event);
        this.close();
    };

    this._onStreamAbort = function(e) {
        this.dispatchEvent(new CustomEvent("abort"));
        this.close();
    };

    this._onStreamProgress = function(e) {
        if (!this.xhr) {
            return;
        }

        if (this.xhr.status !== 200) {
            this._onStreamFailure(e);
            return;
        }

        if (this.readyState == this.CONNECTING) {
            this.dispatchEvent(new CustomEvent("open"));
            this._setReadyState(this.OPEN);
        }

        var data = this.xhr.responseText.substring(this.progress);
        this.progress += data.length;
        data.split(/(\r\n|\r|\n){2}/g).forEach(
            function(part) {
                if (part.trim().length === 0) {
                    this.dispatchEvent(this._parseEventChunk(this.chunk.trim()));
                    this.chunk = "";
                } else {
                    this.chunk += part;
                }
            }.bind(this)
        );
    };

    this._onStreamLoaded = function(e) {
        this._onStreamProgress(e);

        // Parse the last chunk.
        this.dispatchEvent(this._parseEventChunk(this.chunk));
        this.chunk = "";
    };

    /**
     * Parse a received SSE event chunk into a constructed event object.
     */
    this._parseEventChunk = function(chunk) {
        if (!chunk || chunk.length === 0) {
            return null;
        }

        var e = {
            id: null,
            retry: null,
            data: "",
            event: "message",
            usage: ""
        };

        chunk.split(/\n|\r\n|\r/).forEach(
            function(line) {
                line = line.trimRight();
                var index = line.indexOf(this.FIELD_SEPARATOR);
                if (index <= 0) {
                    // Line was either empty, or started with a separator and is a comment.
                    // Either way, ignore.
                    return;
                }

                var field = line.substring(0, index);
                if (!(field in e)) {
                    return;
                }
   
                var value = line.substring(index + 1).trimLeft();
                // var valuee = value.replace("Zabbix", "Synthesis");
                if (field === "data") {
                    e[field] += value;
                } else {
                    e[field] = value;
                }
            }.bind(this)
        );

        var event = new CustomEvent(e.event);
        event.data = e.data;
        event.id = e.id;
        return event;
    };

    this._checkStreamClosed = function() {
        if (!this.xhr) {
            return;
        }

        if (this.xhr.readyState === XMLHttpRequest.DONE) {
            this._setReadyState(this.CLOSED);
        }
    };

    this.stream = function() {
        this._setReadyState(this.CONNECTING);

        this.xhr = new XMLHttpRequest();
        this.xhr.addEventListener("progress", this._onStreamProgress.bind(this));
        this.xhr.addEventListener("load", this._onStreamLoaded.bind(this));
        this.xhr.addEventListener(
            "readystatechange",
            this._checkStreamClosed.bind(this)
        );
        this.xhr.addEventListener("error", this._onStreamFailure.bind(this));
        this.xhr.addEventListener("abort", this._onStreamAbort.bind(this));
        this.xhr.open(this.method, this.url);
        for (var header in this.headers) {
            this.xhr.setRequestHeader(header, this.headers[header]);
        }
        this.xhr.withCredentials = this.withCredentials;
        this.xhr.send(this.payload);
    };

    this.close = function() {
        if (this.readyState === this.CLOSED) {
            return;
        }

        this.xhr.abort();
        this.xhr = null;
        this._setReadyState(this.CLOSED);
    };
};

// Export our SSE module for npm.js
if (typeof exports !== "undefined") {
    exports.SSE = SSE;
}

var textarea = document.getElementById('ai_textarea');
var this_filepath = '<?php echo $this_filepath; ?>';

var communicator = '';
var fixedprompt = '<?php echo $fixedprompt;?>'
var prompt = '<?php echo $fixedprompt; echo $prob_name;?>'
// console.log(prompt);
if(prompt != ''){
    autosize(textarea);
    (textarea.innerHTML == '') ? communicator = 'You: ' : communicator = '\n\nYou: '
    // use SSE to get server Events
    var source = new SSE("/synthesis/dashboard/problems/request.php?prompt=" +prompt);

    source.addEventListener('message', function (e) {

        if(e.data){
            if(e.data != '[DONE]'){
                var tokens = JSON.parse(e.data).choices[0].text
                textarea.innerHTML += tokens
                autosize.update(textarea)
                bottomScroll();
            }else{
                textarea.innerHTML +=  "This response was generated by Synthesis SmartSolver and may require review by administrator."
                console.log('Completed');
            }
        }
    })
    source.stream()
}


function bottomScroll(){
    textarea.scrollIntoView(false)
    textarea.scrollTo(0, textarea.scrollHeight)
}
</script>
