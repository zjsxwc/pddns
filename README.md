
# pddns

由于家里路由器公网ip老是变动，于是有了这个想法，通过公网dns服务器，把域名解析到家里的电脑ip


1. 命令行 cli 运行 `cliDaemon.php` 守护进程来作为dns服务器

2. 家里的电脑浏览器访问`/index.php?fakeDomain=fake.com&ip=191.43.556.77`来更新dns服务器域名与ip的对应映射

3. 手机等想访问家里电脑的设备的dns地址添加这个dns服务器

