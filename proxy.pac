function _proxy_website(url){

	var websites_array = [ '*.xunlei.com/*', 
							'*.baidu.com/*' ];
							//'*.youku.com/*',
							//'*.tudou.com/*'];

	for(i=0; i<websites_array.length; i++){
		if(shExpMatch(url, websites_array[i]))
			return true;
	}

	return false;
}

function FindProxyForURL(url, host) {
	
	//alert(_proxy_website(url));
    alert(url);
	alert(host);
    if( _proxy_website(url)){

		return "SOCKS5 localhost:1080";
	}
	
    return "DIRECT";
}
