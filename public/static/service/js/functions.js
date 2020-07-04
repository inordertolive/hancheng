// 转义聊天内容中的特殊字符
function replaceContent(content) {
    // 支持的html标签
    var html = function (end) {
        return new RegExp('\\n*\\[' + (end || '') + '(pre|div|span|p|table|thead|th|tbody|tr|td|ul|li|ol|li|dl|dt|dd|h2|h3|h4|h5)([\\s\\S]*?)\\]\\n*', 'g');
    };
    content = (content || '').replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;')
        // .replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;').replace(/"/g, '&quot;') // XSS
        .replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&#39;/g, "'").replace(/&quot;/g, '"') // XSS
        .replace(/@(\S+)(\s+?|$)/g, '@<a href="javascript:;">$1</a>$2') // 转义@

        .replace(/emoji\[([^\s\[\]]+?)\]/g, function (face) {  // 转义表情
            var alt = face.replace(/^emoji/g, '');
            return '<img alt="' + alt + '" title="' + alt + '" src="' + faces[alt] + '">';
        })
        .replace(/img\[([^\s]+?)\]/g, function (img) {  // 转义图片
            return '<img class="layui-whisper-photos" src="' + img.replace(/(^img\[)|(\]$)/g, '') + '" width="100px" height="100px">';
        })
        .replace(/file\([\s\S]+?\)\[[\s\S]*?\]/g, function (str) { // 转义文件
            var href = (str.match(/file\(([\s\S]+?)\)\[/) || [])[1];
            var text = (str.match(/\)\[([\s\S]*?)\]/) || [])[1];
            if (!href) return str;
            return '<a class="layui-whisper-file" href="' + href + '" download target="_blank"><i class="layui-icon">&#xe61e;</i><cite>' + (text || href) + '</cite></a>';
        })
        .replace(/a\([\s\S]+?\)\[[\s\S]*?\]/g, function (str) { // 转义链接
            var href = (str.match(/a\(([\s\S]+?)\)\[/) || [])[1];
            var text = (str.match(/\)\[([\s\S]*?)\]/) || [])[1];
            if (!href) return str;
            return '<a href="' + href + '" target="_blank">' + (text || href) + '</a>';
        }).replace(html(), '\<$1 $2\>').replace(html('/'), '\</$1\>') // 转移HTML代码
        .replace(/\n/g, '<br>') // 转义换行
		console.log(content,'转义后的东西');
    return content;
};

// 表情替换
var faces = function () {
    var alt = getFacesIcon(), arr = {};
    layui.each(alt, function (index, item) {
		if(Number(index) >= 100){
			arr[item] = '/static/images/emoji/' + (index+100) + '.png';
		}else{
			arr[item] = '/static/images/emoji/' + (index+100) + '.gif';
		}
    });
    return arr;
}();

// 表情对应数组
function getFacesIcon() {
    return ["[微笑]","[伤心]","[美女]","[发呆]","[墨镜]","[哭]","[羞]","[哑]","[睡]","[哭]","[囧]","[怒]","[调皮]","[笑]","[惊讶]","[难过]","[酷]","[汗]","[抓狂]","[吐]","[笑]","[快乐]","[奇]","[傲]","[饿]","[累]","[吓]","[汗]","[高兴]","[闲]","[努力]","[骂]","[疑问]","[秘密]","[乱]","[疯]","[哀]","[鬼]","[打击]","[bye]","[汗]","[抠]","[鼓掌]","[糟糕]","[恶搞]","[什么]","[什么]","[累]","[看]","[难过]","[难过]","[坏]","[亲]","[吓]","[可怜]","[刀]","[水果]","[酒]","[篮球]","[乒乓]","[咖啡]","[美食]","[动物]","[鲜花]","[枯]","[唇]","[爱]","[分手]","[生日]","[电]","[炸弹]","[刀子]","[足球]","[瓢虫]","[翔]","[月亮]","[太阳]","[礼物]","[抱抱]","[拇指]","[贬低]","[握手]","[剪刀手]","[抱拳]","[勾引]","[拳头]","[小拇指]","[拇指八]","[食指]","[ok]","[情侣]","[爱心]","[蹦哒]","[颤抖]","[怄气]","[跳舞]","[发呆]","[背着]","[伸手]","[耍帅]","[微笑]","[生病]","[哭泣]","[吐舌]","[迷糊]","[瞪眼]","[恐怖]","[忧愁]","[眨眉]","[闭眼]","[鄙视]","[阴暗]","[小鬼]","[礼物]","[拜佛]","[力量]","[金钱]","[蛋糕]","[彩带]","[礼物]" ]
}