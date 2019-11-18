M.block_jobonline =
    {
        init: function()
        {
            var me=document.getElementById('jobonlineblockwrapper');

            this.lockdown(document.getElementById('jobonlineblockwrapper'));

            console.log('block_jobonline js loaded and locked');

            if(me.getAttributeNode('bounce').value==='1')
            {
                var me=document.getElementsByClassName('block_jobonline')[0];
                var tgt=document.getElementsByClassName('course-content')[0];
                tgt.insertBefore(me,tgt.firstChild);
            }

            return true;
        },

        // Strip out any embedded styles in the feed
        lockdown: function(node)
        {
            // Node type 1 is an element.
            if(node.nodeType==1)
            {
                node.removeAttribute('style');
            }
            node=node.firstChild;
            while(node)
            {
                this.lockdown(node);
                node=node.nextSibling;
            }
        },

        close_collapsible: function(me,event)
        {
            if(!event.ctrlKey) //Hold down ctrl to NOT close other jobs
            {
                var temp=me.getAttributeNode('state').value;
                var elements=document.getElementsByClassName('jobonline_job');
                for(i=0;i<elements.length;i++)
                {
                    elements[i].getAttributeNode('state').value=0;
                }
                me.getAttributeNode('state').value=temp;
            }
        },

        updateitem: function(obj,contents)
        {
            if(typeof contents !== 'undefined'  && contents !== 'failed')
            {
                obj.innerHTML=contents;
            }
        },

        // func is the brain function
        // params is an array
        setitem: function(obj,func,params)
        {
            obj.style.filter='blur(1px)';
            var r=new XMLHttpRequest;
            r.onload = function (e) {
                if (r.readyState === 4)
                {
                    if (r.status === 200)
                    {
                        M.block_jobonline.updateitem(obj,r.responseText);
                    }
                }
                obj.style.filter='blur(0px)';
            }

            r.onerror=function(e)
            {
                obj.style.filter='blur(0px)';
            }

            r.ontimeout=function(e)
            {
                obj.style.filter='blur(0px)';
            }

            r.open('GET','/blocks/jobonline/brain.php?fn='+func+'&'+encodeURI(params)+'&'+Date.now());
            r.send(null);
        },

        setlocation: function(obj,user)
        {
            this.setitem(document.getElementById('jobonlineblockwrapper'),'update_location',
                         ['user='+user,'location='+obj.value].join('&'));
        },

        setsector: function(obj,user)
        {
            this.setitem(document.getElementById('jobonlineblockwrapper'),'update_sector',
                         ['user='+user,'sector='+obj.value].join('&'));
        },

        settype: function(obj,user)
        {
            this.setitem(document.getElementById('jobonlineblockwrapper'),'update_type',
                         ['user='+user,'type='+obj.value].join('&'));
        },

        setsort: function(obj,user)
        {
            this.setitem(document.getElementById('jobonlineblockwrapper'),'update_sort',
                         ['user='+user,'sort='+obj.value].join('&'));
        },

        test: function(object,text)
        {
            object.innerHTML='AAAA'+text;
        },
    };
