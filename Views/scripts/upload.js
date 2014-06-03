/*global FileReader, FormData */
/*jslint vars: true, plusplus: true, devel: true, regexp: true, nomen: true, indent: 4, maxerr: 50 */

window.onload = function () {
    "use strict";

    var holder = document.getElementById('holder'),
        tests = {
            filereader: typeof FileReader !== 'undefined',
            dnd: 'draggable' in document.createElement('span'),
            formdata: !!window.FormData,
            progress: "upload" in new XMLHttpRequest()
        },
        support = {
            filereader: document.getElementById('filereader'),
            formdata: document.getElementById('formdata'),
            progress: document.getElementById('progress')
        },
        fileupload = document.getElementById('upload'),
        elements = ['filereader', 'formdata', 'progress'];

    elements.forEach(function (api) {
        if (tests[api] === false) {
            support[api].className = 'fail';
        } else {
            // FFS. I could have done el.hidden = true, but IE doesn't support
            // hidden, so I tried to create a polyfill that would extend the
            // Element.prototype, but then IE10 doesn't even give me access
            // to the Element object. Brilliant.
            support[api].className = 'hidden';
        }
    });
    
    function createPanel(name, status) {
        var panelzone = document.getElementById('upload-panelzone');
        if (!panelzone) {
            return false;
        }

        var char = '<div class="panel panel-default" data-dismiss="alert"><button type="button" class="close" id="' + name.split('.')[0] + '">&times;</button><div class="panel-heading">' + name + '</div><div class="panel-body"><div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"><span class="sr-only">0%</span></div></div></div></div>';

        var element = document.createElement("div");
        element.className = "col-md-4";
        element.innerHTML = char;

        panelzone.appendChild(element);

        $('#' + name.split('.')[0]).bind('click', function () {
            var xhr = new XMLHttpRequest();
            var url = "index.php?action=deleteuploadedfile";
            
            if(status == 'new')
                var params = 'class=' + name.split('.')[0] + '&status=' + status;
            else(status == 'old')
                var params = 'class=' + name.split('.')[0] + '&status=' + status;

            xhr.open("POST", url, true);
            //Send the proper header information along with the request
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.setRequestHeader("Content-length", params.length);
            xhr.setRequestHeader("Connection", "close");

            xhr.send(params);
        });

        return element;
    }

    function loadFile(file) {
        var formData = tests.formdata ? new FormData() : null,
            element,
            progressBar,
            filename;

        formData.append('file', file);
        
        filename = file.name.length > 20 ? file.name.substring(0, 15) + '...' : file.name;
        element = createPanel(filename,  'old');
        if (element === false) { // if the element is false, we only have to upload a single file
            element = document.getElementById('upload-uniquefile');
            element.getElementsByClassName('panel-heading')[0].innerHTML = filename;
        }
        
        progressBar = element.getElementsByClassName('progress-bar')[0];

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'index.php?action=uploadtmp');
        xhr.onload = function (progressBar) {
            this.progressBar.style.width = '100%';
            this.progressBar.setAttribute('aria-valuenow', 100);
            this.progressBar.innerHTML = 100;
        }.bind({progressBar: progressBar});

        if (tests.progress) {
            xhr.upload.onprogress = function (event) {
                if (event.lengthComputable) {
                    var complete = (event.loaded / event.total * 100);
                    this.progressBar.style.width = complete;
                    this.progressBar.setAttribute('aria-valuenow', complete);
                    this.progressBar.innerHTML = complete;
                }
            }.bind({progressBar: progressBar});
        }

        xhr.send(formData);
    }

    function readFiles(files) {
        var i;
        for (i = 0; i < files.length; i++) {
            loadFile(files[i]);
        }
    }

    if (tests.dnd) {
        holder.ondragover = function () { this.className = 'hover'; return false; };
        holder.ondragend = function () { this.className = ''; return false; };
        holder.ondrop = function (e) {
            this.className = '';
            e.preventDefault();
            readFiles(e.dataTransfer.files);
        };
    } else {
        fileupload.className = 'hidden';
        fileupload.querySelector('input').onchange = function () {
            readFiles(this.files);
        };
    }
};