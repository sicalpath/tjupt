(function(){
		
	var UNDEFINED,
	
	window = this,
	
	emotionsData=window.emotionsData,
	
    slice = Array.prototype.slice,
	
    toString = Object.prototype.toString,
	
	isIE6 = !!($.browser.msie && $.browser.version == '6.0'),
    isFn = $.isFunction,
    emotionPath = ''
    ;

	/**
	* 扩展
	* @return {Function|Object} 返回扩展后的子对象
	* @param {Function|Object} c 子对象
	* @param {Function|Object} p 父对象
	*/
	function extend(c, p) {
	    var
	        _cType = toString.call(c).replace(/(?:^.* )|\]/g,''),
	        _pType = toString.call(p).replace(/(?:^.* )|\]/g,''),
	        _cache;
	        
	    if(_cType === 'Object') {
	        if(_pType === 'Function') {
	            p = new p();
	        }
	        for(var attr in p) {
	            c[attr] || (c[attr] = p[attr]);
	        }
	    }else if(_cType === 'Function') {
	        _cache = c.prototype;
	        if(_pType === 'Function') {
	            c.prototype = new p();
	        }else {
	            for(var attr in p) {
	                c.prototype[attr] || (c.prototype[attr] = p[attr]);
	            }   
	        }
	        for(var attr in _cache) {
	            c.prototype[attr] = _cache[attr];
	        }
	        
	        c.prototype.constructor = c;
	    }
	    
	    _cType = _pType = _cache = null;
	    
	    return c;
	};
	
	/**
	* 模块管理对象
	*/    
	var modMgr = {
	    /**
	    * 模块缓存
	    */
	    mod: {},
	    /**
	    * 实例缓存
	    */
	    instances: {},
	    /**
	    * 注册模块
	    * @param {String} name 模块名称
	    * @param {Funtion|Object} ctor 模块构造器或者对象
	    * 可带参数，用于produce方法，模块实例初始化时传入
	    */
	    regist: function(name, ctor /*arguments*/) {
	        if(this.mod[name] === UNDEFINED) {
	            this.mod[name] = {};
	        }
	        
	        this.mod[name].ctor = ctor;
	        this.mod[name].args = slice.call(arguments, 2);
	    },
	    
	    /**
	    * 模块产生
	    * @return {Object} 模块实例或副本
	    * @param {String} name 格式: '模块名称|模块别名' 
	                    模块名和别名用'|'分隔，用于区分同个模块不同实例
	    * 可带参数，这里参数的优先级高于注册时的参数
	    */
	    produce: function(name /*arguments*/) {
	        var _name = name.split('|');
	        var aname = _name[1];
	        name = _name[0];
	        _name = null;
	        
	        if(!this.mod[name]) {return ;}
	        
	        var newObj, _args = slice.call(arguments, 1);
	        
	        if(_args.length === 0) {
	            _args = this.mod[name].args;
	        }
	        
	        if(isFn(this.mod[name].ctor)) {
	            newObj = new this.mod[name].ctor();
	        }else {
	            newObj = this.mod[name].ctor;
	        }
	        
	        aname = aname || name;
	        if(this.instances[aname]) {
	            extend(this.instances[aname], newObj);
	        }else {
	            this.instances[aname] = newObj;
	        }        
	        
	        newObj.init && newObj.init.apply(newObj, _args);
	        
	        _args = null;
	        
	        return newObj;
	    },
	    
	    /**
	    * 模块销毁
	    * @param {String} name 模块名称
	    */
	    remove: function(name) {
	        if(this.mod[name]) {
	            
	            this.mod[name].destroy && this.mod[name].destroy();
	            
	            this.mod[name] = null;
	            delete this.mod[name];
	        }
	    },
	    
	    /**
	    * 调用模块实例的方法
	    * @return 模块实例的方法的返回值
	    * @param {String} name 模块别名
	    * @param {String} fn 模块方法名
	    */
	    notify: function(name, fn /*arguments*/) {
	        var returnValue, _obj = this.instances[name];
	        
	        if(_obj && _obj[fn]) {
	            var _args = slice.call(arguments, 2);
	            
	            returnValue = _obj[fn].apply(_obj, _args);
	            
	            _args = null;
	        }
	        
	        _obj = null;
	        
	        return returnValue;
	    }
	};
	
	/**
	 * 所有UI组件都继承于uiComponent
	 */
	var uiComponent = function() {
	    this.cid = 'uiComponent'; //组件的类型ID
	    this.element = null; //组件的DOM
	    this.relArray = [];
	};
	
	uiComponent.prototype = {
	    init: function(data, callback, scope) {
	        if(isFn(callback)) {
	            callback.call(scope || this, this);
	        }
	    },
	    config: function(option) {
	        $.extend(this, option);
	        if(this.rel){
	        	($.inArray(this.rel,this.relArray)<0)&&this.relArray.push(this.rel);
	        }
	    },
	    show: function() {
	        $(this.element).show();
	    },
	    hide: function() {
	        $(this.element).hide();
	    },
	    remove: function() {
	        $(this.element).remove();
	        this.element = null;
	    }
	};
    
	/**
	 * 浮层组件
	 */
	 var layer = function() {
	    this.cid = "layer";
	    this.rel = null;
	    this.ox = 0;
	    this.oy = 0;
	    this.showCallback = null;
	    this.closeCallback = null;
		this.pos=0;//0 将浮层插入到页面最下面,1将浮层插入到触发事件元素的同级最底层,@center将浮层设置成fixed
		this.showStyle=0;
	 };
	 layer.prototype = {
	    init: function(data) {
	        this.element = data.el;
	        this.rel = data.rel || document.body;
	        $(this.element).css({
	            'position': 'absolute',
	            'z-index': ++$.layerIndex
	        });
	    },
	    
	    show: function() {
	    	this.rel&&$(this.rel).attr('hide','1');//
	    	//
	    	switch(this.showStyle){
	    		case 1:
	    			this.showCallback?$(this.element).slideDown('fast',this.showCallback):$(this.element).slideDown('fast');
	    			break;
	    		default:
	    			$(this.element).show();
	    		break;
	    	}
	        $(this.element).css('z-index', ++$.layerIndex);
	        this.resize();
	        this.setCommonEvent();
	        isFn(this.showCallback) && this.showCallback(this);
	        return false;
	    },
	    
	    close: function() {    
			$(this.element).hide()
			if(this.rel){//
				$(this.rel).attr('hide','0');
			}
	        isFn(this.closeCallback) && this.closeCallback(this);
	    },
	    
	    resize: function() {
	        var offset,ox,oy;
				switch (this.pos){
					case 'center':
						isIE6||$(this.element).css('position','fixed');
						break;
					default:
						if(this.pos==1){
							var $parent=findParentElement('DIV',$(this.rel));
							$parent.css('position','relative');
							$parent.append(this.element);					
						}				
						offset = this.pos?$(this.rel).position():$(this.rel).offset();
						ox = offset.left + this.ox;
						oy = offset.top + this.oy;
						$(this.element).css({
							top: oy,
							left: ox
						});
						break;
				}
	    },
	    
	     /**
	     * 设置组件公有事件
	     */
	    setCommonEvent: function() {
	        $(document.body).one('click', this, function(e) {
	            var obj = e.data;
	            if(obj.element != e.target && !$.contains(obj.element, e.target)) {
	                obj.close();
	            }else {
	                obj.element.offsetHeight && obj.setCommonEvent();
	            }
	        });
            
	        
	        $(window).bind('resize', (function(obj) {
	            return function() {obj.resize()};
	        })(this));
			
			$(window).bind('keyup',this,function(e){
				if(e.keyCode==27){
					e.data.close();
				};
			});
	    }
	    
	 };
	 extend(layer, uiComponent);
	 
	 /**
	 * 窗口组件
	 * 继承自浮层组件
	 */
	 var xwin = function() {
	    this.cid = "xwin";
	    this.isShowBg = true; //是否显示背景
	    this.bg = null //显示对应DOM
	 };
	 xwin.prototype = {
	    init: function(data) {
	        this.element = data.el;
	        
	        $(this.element).css({
            //'position': 'absolute',
	            'z-index': ++$.layerIndex
	        });
            
            $(window).resize(function(obj) {
                return function() {
                    obj.resize();
                }
            }(this));
            
	    },
	    
	    show: function() {
	        this.isShowBg &&  this.showBg();
	        
	        $(this.element).show();
	        
	        this.resize();
	        
	        this.setCommonEvent();

			
	        isFn(this.showCallback) && this.showCallback(this);
	    },
		
	    
	     /**
	     * 设置组件公有事件
	     */
	    setCommonEvent: function() {
			$(window).bind('keyup',this,function(e){
				if(e.keyCode==27){
					e.data.close();
				};
			});
	    },
		
		remove: function(){
			this.close();
			$(this.element).remove();
			this.bg && $(this.bg).remove();
		},
	    
	    close: function() {
	        $(this.element).hide();
	        
	        if(this.isShowBg) {
                $(this.bg).hide();
            }
            
	        isFn(this.closeCallback) && this.closeCallback(this);
	    },
	    
	    showBg: function() {
	
	        if(!this.bg) {
	            this.bg = $('<div></div>')
	                .addClass('shade-div')
	                .css({
	                    'z-index': 10000
	                })
	                .appendTo($(document.body))[0];
                                   
	        }
	        
	        $(this.bg).show();
	    },
	    
	    resize: function() {
            //这里会引起某些IE浏览器透明度滤镜失效 超过4096px ati显卡
            var wholeHeight = parseInt(document.documentElement.clientHeight);            
            if(this.bg){
                $(this.bg).height(wholeHeight);
            };
            
	        // $(this.element).css({
	            // 'top': ($w.height() - this.element.offsetHeight)/2,
	            // 'left': ($w.width() - this.element.offsetWidth)/2
	        // });
	    }
	 };
	 extend(xwin, layer);
	 
	/**
	 * 表情浮层组件
	 * 继承自浮层组件
	 */
	 var emotion = function() {
	    this.cid = 'emotion';
	    this.input = null;
	    this.ctn = null;
	    this.eList = null;
	 };
     
	 emotion.prototype = {
	    init: function(data) {
	        this.element = data.el;
	        this.rel = data.rel || document.body;
	        this.ctn = data.emotionArea || this.element;
	        
	        $(this.element).css({
	            'position': 'absolute',
	            'z-index': ++$.layerIndex
	        });
	        $(this.ctn).bind('click', this, function(e) {
	            var obj = e.data,
	            	num = null,mtype,
	            	$target = $(e.target);
	            $(this).children().each(function(i, item) {
	                if((item == e.target || $.contains(item, e.target))&&!$target.hasClass('play_btn')) {
	                    num = i;   
	                    num = $(e.target).parent().attr('title');
						mtype = $(e.target).parent().attr('mtype')
						if (mtype=='face'){
				            (num == null) || obj.insert('['+num+']');
						}
						else if(mtype=='ani'){
							(num == null) || obj.insert('['+num+']');
						}
						else
						{
							return false;	
						}
						
	                    obj.hide();
	                    
	                    return false;
	                }
	            });
	        });
	    },
	    
	    insert: function(num) {
	        var text = num;//this.eList[num];
	        text && this.input && $(this.input).insertText(text);
            $(this.input).focus();
            
            this.insertCallback && this.insertCallback(this.input);
	    }
	 };
	 extend(emotion, layer);
	 
	$.extend({
	    'modMgr': modMgr,
        'prefix': {
            'layer': 'xweibo_layer_',
            'xwin': 'xweibo_win_',
            'emotion': 'xweibo_emo_'
        },
	    'uicid': 0,
        'layerIndex': 10000000,
	
	    /**
	    * 获取指定组件的实例
	    */
	    getInstance: function(type, config) {
	        var
	            obj,
	            el = config.el,
	            instances = $.modMgr.instances,
	            prefix = $.prefix[type];
	         
	        if(el.id && instances[prefix+el.id]) {
	            obj = instances[prefix+el.id];
	        }else {
	            el.id || (el.id = prefix+ ++$.uicid);                
	            obj =  $.modMgr.produce(type+'|'+prefix+el.id, config);
	        }
	        
	        return obj;
	    }
	

	});
	
	//组件注册
	modMgr.regist('layer', layer);
	modMgr.regist('xwin', xwin);
	modMgr.regist('emotion', emotion);
	
	//扩展至jQuery
	$.extend($.fn, {
       
	    /**
	    * 形成浮层对象
	    *@return {Object} layer实例对象
	    */
	    toLayer: function() {
	
	        var layer;
	        
	        this.each(function() {           
	            
	            layer = $.getInstance('layer', {'el':this});
	
	        });
	        
	        return layer;
	    },
	    /**
	    * 形成窗口对象
	    *@return {Object} xwin实例对象
	    */
	    toXwin: function() {
	
	        var xwin;
	        
	        this.each(function() {           
	            
	            xwin = $.getInstance('xwin', {'el':this});
	
	        });
	        
	        return xwin;
	    },
	    
	    /**
	    * 形成表情浮层对象
	    *@return {Object} emotion实例对象
	    *@param {Object} opt  表情组件配置对象
	        opt.emotionArea 设置表情列表的容器
	    */
	    toEmotion: function(opt) {
	
	        var emo;
	        
	        this.each(function() {
	            
	            emo = $.getInstance('emotion', {'el':this, 'emotionArea': opt.emotionArea});
	
	        });
	        
	        return emo;
	    },
	    	    /**
	    * 插入文字到光标处
	    *@return {Object} jQuery对象.
	    *@param {String} text  要插入的文字
	    */
	    insertText: function(text) {
	        this.each(function() {
	            if(this.tagName !== 'INPUT' && this.tagName !== 'TEXTAREA') {return;}
	            if (document.selection) {
	                this.focus();
	                var cr = document.selection.createRange();
	                cr.text = text;
                    cr.collapse();
                    cr.select();
	            }else if (this.selectionStart || this.selectionStart == '0') {
	                var 
	                    start = this.selectionStart,
	                    end = this.selectionEnd;
	                this.value = this.value.substring(0, start)+ text+ this.value.substring(end, this.value.length);
                    
                    this.selectionStart = this.selectionEnd = start+text.length;
                    
	            }else {
	                this.value += text;
	            }
	        });        
	        return this;
	    },
	    showEmotion:function(param){
			var _param={faceBtn:$(this)};
			param = $.extend(_param,param);
			if(!param.input){alert('参数配置错误，请设置文本框jq对象');return;}
	    	wbSubjoin.eventBind(param)
	    },
	    listEmotion:function(){
	    	$(this).each(function(){
	    		var	returnValue = $(this).html();
	    		returnValue = returnValue.replace(/(\[[^\[\]]*\])/g,
					function($1){
						var temp = '';
						$.each(emotionsData,function(i,it){
							if($1 == '[mo.' + it[0] + ']'&&it[4]=='ani'){
								temp='<div class="magic_emotion" ><a class="magic_link" rel="magic" href="javascrpt:void(0)">&nbsp;</a><img src="' + it[1].replace('.swf','.gif') + '" title="' + it[0] + '"/></div>';
							     return false;								
							}
							else if($1 == '[' + it[0] + ']'){
							     temp = '<img src="' + it[1] + '" title="' + it[0] + '"/>';
							     return false;
							}else{
								temp = $1;
							}
						});
						return temp;
					})
	    		$(this).html(returnValue);
	    	})
	    	$('a[rel=magic]').live('click',function(){
	    		var src = $(this).siblings('img').attr('src').replace('.gif','.swf');
	    		wbSubjoin.showMagicEmotion(src);
	    		return false;
	    	});
	    } 			
	    
	});

    
layerMgr = (function() {

    return {
        layers: {
            'default': {}
        },
        
        showLayer: function(id, group) {
            var layer = this.get(id, group);
            
            if(!layer) {return;}                      
            if($(layer.rel).attr('hide')=='1'){
            	layer.close();
            	return;           	
            }
            var layers = this.layers[layer['group']];
            
            for(var i in layers) {
                layers[i].hide();
            }
            //设置管理器内所有按钮状态
            $.each(layer.relArray,function(i,rel){
            	$(rel).attr('hide','0');           	
            })           
	
            layer.show();

        },

        addLayer: function(option) {
            var 
                $layer, layer, isNew,
                layers = this.layers,
                type = option.type,
                id = option.layerid||'',
                group = option.group||'default',
                html = option.content,
                config = option.config,
                addClass = option.addClass,
                noarrow = option.noarrow,//不加载箭头
                noclose = option.noclose;//不加载关闭按钮
            
            if(!layers[group]) {
                layers[group] = {};
                isNew = true;
            }else if(!layers[group][id]) {
                isNew = true
            }else {
                layer = layers[group][id];
            }
            
            if(isNew) {
                $layer = this.create(id, group, html,noarrow,noclose);
                addClass&& $layer.addClass(addClass);
                switch(type) {
                    case 'emotion':
                        $layer.addClass('emotion-window');
                        layer = $layer.toEmotion({
                            'emotionArea': $layer.find('div.emotion_content')//('div.emotion-box')//[0]
                        });
                        break;              
                }
                
                config && layer.config(config);
                

                $layer.find('a.close-btn').click(function(e) {
                    layer.close();
                    e.preventDefault();
                });
            }
            
            layers[group][id] = layer;
            layer.group = group;
            
            return layer;
        },
        
        get: function(id, group) {
            var layer = null, range = this.layers[group] || this.layers;
            
            for(var i in range) {
                if(!group) {
                    layer = this.get(id, i);
                    if(layer) {
                        break;
                    }                    
                }else {
                    layer = this.layers[group][id];
                }
            }
            
            return layer;
        },
        
        create: function(id, group, html, noarrow, noclose) {
            var $layer, $content, isNew = false;

            html = html || '';
            
            $layer = $('<div></div>')
                .attr('id', id)
                .addClass('pop-window')
                .html([
                    '<div class="pop-t"></div>',
                    '<div class="pop-content">'+html+'</div>',
                    '<div class="pop-b"></div>',
					
                    '<div class="pop-tl all-bg"></div>',
                    '<div class="pop-tr all-bg"></div>',
					
                    '<div class="pop-bl all-bg"></div>',
                    '<div class="pop-br all-bg"></div>'
                ].join(''))
                .appendTo($(document.body));
                noclose || $layer.append('<a class="close-btn icon-bg" href="javascript:;" title="关闭"></a>');
                noarrow || $layer.append('<div class="arrow all-bg"></div>');
				$layer.hide();
            
            return $layer;
        },
        
        setContent: function(id, html) {
            var layer = this.get(id);
            layer&&$(layer.element).find('div.pop-content').html(html);           
        }
    };
})();
    
function findParentElement(tagName, $ele){
        $parent = $ele.parent();
        
        if (!$parent.length) {
            return null;
        }
        
        if ($parent.get(0).tagName == tagName) {
            return $parent;
        }
        else {
            return findParentElement(tagName, $parent);
        }
}

	var wbSubjoin = (function() {
		return {
			eventBind: function(param){
				var $faceBtn = param.faceBtn,
					$input = param.input,
					ox = param.ox|| -10,
					oy = param.oy|| 0
					;
				
					$faceBtn.bind('click', function(e) {
							wbSubjoin.showEmotion(this, $input, ox, oy);
							return false;
					});			
			},
			init : function() {
				this.createEmotion();
			},
			getEmotionBox : function(emotions, mtype) {
				var prefix = emotionPath, content = '', eList = [], This = this, pWidth, pHeight;
				(mtype == 'face')
						? (pWidth = pHeight = 22)
						: (pWidth = pHeight = 56);
				// **开始
				var lang = $DMBMSG.DMB1000, emotionsClsArray = [], emotionsCls = {}, emotionsClsStr = "";
				$.each(emotions, function(i, emotion) {
							if (lang != emotion[3])
								return;
							if (emotion[4] == mtype) {
								(emotion[2] == "")
										&& (emotion[2] = $DMBMSG.DMB120);
								if ($.inArray(emotion[2], emotionsClsArray) < 0) {
									emotionsClsArray.push(emotion[2]);
									emotionsCls[emotion[2]] = new Array()
								};
								emotionsCls[emotion[2]].push(emotion);
							}
						});
				content = "";
				emotionsClsStr = '<div class="emotiongroup">';
				var emotionsNum = 0;
				for (j = 0; j < emotionsClsArray.length; j++) {
					var display, strlen, aniclass, k = 1;
					// 构建表情分组名称
					(mtype == 'ani') && (aniclass = 'ani');
					strlen = emotionsClsArray[j].length * 12 + 15+8;
					emotionsNum += strlen;
					if (emotionsNum > 330) {
						emotionsClsStr += '</div><div class="emotiongroup">';
						emotionsNum = strlen
					}
					emotionsClsStr += '<a href="javascript:void(0)">'
							+ emotionsClsArray[j]
							+ '</a><span class="line">&nbsp;</span>';

					// 构建表情分组内容
					(j != 0) && (display = "none");
					content += '<div class="emotion-box" style="display:'
							+ display + '"><div class="emotion_boxs">';
					$.each(emotionsCls[emotionsClsArray[j]], function(i, item) {
						content += '<a title="' + item[0] + '" mtype="' + mtype
								+ '" class="' + aniclass
								+ '" href="javascript:;"><img src="' + prefix
								+ item[1].replace('.swf', '.gif') + '" width="'
								+ pWidth + '" height="' + pHeight + '" />'
						if (mtype == 'ani') {
							content += '<span>'
									+ item[0]
									+ '</span></a>';
							// 动漫表情内容分页
							(k % 21 == 0)
									&& (content += '</div><div class="emotion_boxs" style="display:none">');
							k++;
						} else {
							content += '</a>';
						}
						eList.push(item[0]);
					});
					content += '</div><div class="clearall"></div>';
					// 添加表情分页信息
					if (mtype == 'ani') {
						content += '<div class="clearall"></div>';
						content += '<div class="emotion_page pagenums"><div class="clearall"></div><span>';
						if ((k - 1) / 21 > 1) {
							content += '<a href="#" class="current">1</a>';
							for (i = 2; i <= ((k - 1) / 21 + 1); i++) {
								content += '<a href="#">' + i + '</a>';
							}
						}
						content += '</span>魔法动画</div>';
					}
					content += '</div>';
				}
				emotionsClsStr += "</div>";
				$('.emotion_list').html(emotionsClsStr);
				$('.emotion_content').html(content);
				// 设置默认状态
				$(".emotion_box_t2 .emotiongroup").each(function(i,$div){$('span',$div).last().remove();});
				$(".emotion_box_t2 .emotion_class").scrollLeft(0);// 默认表情分组
				$('.emotion_box_t2 .emotion_page a').first().attr('class',
						'page_left_no');// 设置分组分页按钮状态_左箭头
				$('.emotion_box_t2 .emotion_page a').last().attr('class',
						'page_right');// 设置分组分页按钮状态_右键头
				$(".emotion_class a:first").attr("class", "current");// 设置默认显示的表情组状态
				//
			},
			getEmotion : function(mtype) {
				var This = this;
				(mtype == 'face') ? $('.emotion_box_t1 a:first').attr('class',
						'current') : $('.emotion_box_t1 a:first').attr('class',
						'');
				(mtype == 'ani') ? $('.emotion_box_t1 a:last').attr('class',
						'current') : $('.emotion_box_t1 a:last').attr('class',
						'');
				$('.emotion_list').html('<div class="loading_bg"></div>');
				$('.emotion_content').html('');
				this.getEmotionData(function() {
							This.getEmotionBox(emotionsData, mtype)
						});
			},
			getEmotionData : function(callback) {
				if (typeof emotionsData != 'undefined') {
					callback && callback();
					return;
				} else {
					$.ajax({
					url:'http://www.weibo.cn/index.php?m=action.emotions',
					dataType: 'json',
					type: 'post',
					success : function(ret){
						if (ret.result) {
								emotionsData = result;
								callback && callback();
								return true;
							}
					},
					error:function(){
						alert('数据加载失败');
					}
				});					
				}
			},
			showMagicEmotion : function(src, param) {
				var id = 'view_ani', html = [
						'<div class="pop-window fixed-window emotion_mag">',
						'<div id="' + id + '"></div>', '</div>'].join('');
				$html = $(html).appendTo('body');
				var $swf = $('#' + id + '', $html), win = $html.toXwin();
				flashParams = {
					id : id,
					quality : "high",
					allowScriptAccess : "never",
					wmode : "transparent",
					allowFullscreen : true,
					allownetworking : "internal"
				}, fwidth = param ? param.width : 440, fheight = param
						? param.height
						: 360, flashVars = {
					playMovie : "true"
				}, zIndex = $('.shade-div').css('z-index');
				swfobject.embedSWF(src, id, fwidth, fheight, "10.0.0", null,
						flashVars, flashParams);
				win.show();
				$('.shade-div').css('z-index', 200000)

				// 自动关闭
				var snap = 0;
				var clock = setInterval(function() {
							var swf = swfobject.getObjectById(id)
							if (swf && swf.PercentLoaded() == 100) {
								try {
									totalFrames = swf.TotalFrames()
								} catch (e) {
									totalFrames = swf.TotalFrames
								}
								curFrame = swf.CurrentFrame();
								// log(totalFrames+'|'+curFrame)
								if (curFrame < totalFrames && curFrame >= snap) {
									snap = curFrame
								} else {
									clearInterval(clock);
									swfobject.removeSWF(id);
									$('.shade-div').css('z-index', zIndex);
									win.remove();
								}
							}
						}, 100)
				$('.shade-div').click(function() {
							clearInterval(clock);
							swfobject.removeSWF(id);
							$('.shade-div').css('z-index', zIndex);
							win.close();
							return false
						});
				return false;
			},
			createEmotion : function() {
				var layer = layerMgr.addLayer({
					layerid : 'xEmotion',
					type : 'emotion',
					config : {
						insertCallback : function(input) {
							$(input).keyup();
						}
					},
					content : [
							'<div class="emotion_box">',
							'<div class="emotion_box_t1 win_title"><a href="javascript:void(0)" mtype="face" class="current">'
									+ $DMBMSG.DMB121
									+ '</a><a href="javascript:void(0)" mtype="ani">'
									+ $DMBMSG.DMB122
									+ '</a></div><div class="clearall"></div>',//
							'<div class="emotion_box_t2">',
							'<div class="emotion_class"><div class="emotion_list">',
							'</div></div>',
							'<div class="emotion_page">',
							'<a href="javascript:void(0)" class="page_left_no">&nbsp;</a>',
							'<a href="javascript:void(0)" class="page_right">&nbsp;</a>',
							'</div>', '</div>', '</div>',
							'<div class="emotion_content">', '</div>'].join('')
				});
				layer.close();

				var This = this, i, ft = 0;
				// 表情组点击事件
				$('.emotiongroup a', layer.element).live('click', function() {
							i = $('.emotiongroup a', layer.element).index(this);
							$(".emotion-box").hide();
							$(".emotion-box").eq(i).show();
							$('.emotion_class a').attr("class", "");
							$(this).attr("class", "current");
						});
				// 表情分组箭头点击事件
				$('.emotion_box_t2>.emotion_page>a').last().click(function() {
							var gourpSize = $('.emotiongroup').size() - 1;
							$(this).prev().attr('class', 'page_left');
							if (ft >= gourpSize) {
								return;
							}
							ft++;
							if (ft == gourpSize) {
								$(this).attr('class', 'page_right_no');
							};
							$(".emotion_box_t2 .emotion_class").scrollLeft(ft
									* 330);
							return false;
						})

				$('.emotion_box_t2>.emotion_page>a').first().click(function() {
							$(this).next().attr('class', 'page_right');
							if (ft <= 0) {
								return;
							}
							ft--;
							if (ft == 0) {
								$(this).attr('class', 'page_left_no');
							};
							$(".emotion_box_t2 .emotion_class").scrollLeft(ft
									* 330);
							return false;
						})
				// 表情分组内容分页
				$('.pagenums a').live('click', function() {
					var $emotions = findParentElement('DIV', $(this))
							.siblings('.emotion_boxs');
					$emotions.hide();
					$(this).siblings().attr('class', '');
					$emotions.eq($(this).text() - 1).show();
					$(this).attr('class', 'current');
					return false;
				});

				$('.emotion_box_t1 a').click(function() {
							ft = 0;
							$('.emotion_box_t1 a').attr("class", "");
							This.getEmotion($(this).attr('mtype'))
						});
				return layer;
			},
			showEmotion : function(rel, input, ox, oy) {
				this.getEmotion('face');
				var emotion = layerMgr.get('xEmotion');

				if (!emotion) {
					emotion = this.createEmotion();
				}

				if (rel instanceof $)
					rel = rel.get(0);

				emotion.config({
							rel : rel,
							input : input,
							ox : ox || 0,
							oy : oy || 30
						});

				layerMgr.showLayer('xEmotion', 'default');
			}
		};

	})();
	//调用初始化对象	
	wbSubjoin.init();
})(jQuery)