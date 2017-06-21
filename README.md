# PHP_web_api

自製的 web_api (簡易框架 + 操作資料庫的 library)

### Log 日誌

* 2016-10-19:
    - 使用 autoLoading 與 namespace，檔案不用再一個一個 include 或 require
* 2016-11-10:
    - 完善 database 操作資料庫的 library，剩下註解未加了
* 2016-11-11:
    - 修改 web_app 路徑下的 libs 之所有檔案
* 2017-01-02:
    - 資料夾目錄的結構與路徑整個重構，並使用 config 裡的檔案預先設定基本資料
* 2017-01-05:
    - Error 導向機制重構，可以更方便與擴充導向 ErrorPage
* 2017-03-10:
    - Library 裡的操作 database 的函式類別都加上註解了
* 2017-03-29:
    - 修改 .htaccess 檔案，修改路由取得 url 的方式
    - 再次重構整個目錄架構，把必要的檔案都放入 core
    - 新增配置類 Conf.php，修改配置路徑
* 2017-04-02:
    - 新增日誌類 Log.php
    - 新增共用函式庫的 dump_debug 函式，專用 Debug
* 2017-06-20:
    - 修改一些標示名稱

### 專案預定目標

* 下次增加:
    - 預計 新增整個自製框架的使用方式
    - 要預先做好 API: 'DB 自動新增 database & table'

