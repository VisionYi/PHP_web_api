# PHP_web_api

自製的web_api (簡易框架 + 操作資料庫的library)

### Log 日誌

* 2016-10-19: 
    - 使用autoLoading與namespace，檔案不用再一個一個include或require了
* 2016-11-10: 
    - 完善database 操作資料庫的library，剩下註解未加了
* 2016-11-11: 
    - 修改web_app路徑下的libs之所有檔案
* 2017-01-02: 
    - 資料夾目錄的結構與路徑整個重構，並使用config裡的檔案預先設定基本資料
* 2017-01-05: 
    - Error導向機制重構，可以更方便與擴充導向ErrorPage
* 2017-03-10:
    - Library裡的操作database的函式類別都加上註解了
* 2017-03-29:
    - 修改.htaccess檔案，修改路由取得url的方式
    - 再次重構整個目錄架構，把必要的檔案都放入core
    - 新增配置類Conf.php，修改配置路徑
* 2017-04-02:
    - 新增日誌類Log.php
    - 新增共用函式庫的dump_debug函式，專用Debug

### 專案預定目標

* 下次增加:
    - 要預先做好API: 'DB自動新增database與table'
    - POST與GET的簡單範例

