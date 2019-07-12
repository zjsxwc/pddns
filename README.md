
# pddns

由于家里路由器公网ip老是变动，
于是有了这个想法，
通过有公网ip的服务器作为dns服务器，
把域名解析到家里的电脑ip


> 这只是穷B不买域名只改设备dns自嗨的一种方式


目前跑脚本系统最好用Linux，Windows我没测过

安卓手机改DNS时需要在wifi里把ip设置为静态

```

1. 命令行 cli 运行 `sudo ./cliDaemon.php` 守护进程来作为dns服务器

2. 家里的电脑浏览器访问`<公网dns服务器的ip>/index.php?fakeDomain=my-home-domain.com&ip=118.89.204.190`
来更新我自己的假域名与家里变动ip的对应映射关系

3. 手机等想访问家里电脑的设备的dns地址添加这个dns服务器ip

```