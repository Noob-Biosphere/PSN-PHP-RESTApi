# 请求更新 token

通过本 URL 获取 Token，用于未来使用。

请求 URL：
```jtext
https://api.azimiao.com/psn/token?auth_key={authkey}&account_id={account_id}&npsso={psn_npsso}&client_id={client_id}
```
参数列表：

1. auth_key：验证key，内测时使用
2. account_id：psn 数字 id，具体获取方法见：xxx
3. npsso：psn 账号 npsso，具体获取方法见：xxx

返回值示例：

```json
{
    "code":200,
    "access_token":"",
    "expires":3599,
    "error":"",
    "error_description":""
}
```
返回值字段：

1. code：请求结果，20x：一切正常，401 auth_key 无效，其他：psn 数据获取失败，详细内容见 error 及 error_description

# 请求某用户的游戏列表

通过本 URL 获取应用/游戏/列表。

请求 URL：
```jtext
https://api.azimiao.com/psn/gamelist?auth_key={authkey}&account_id={account_id}&npsso={psn_npsso}&token={token}&type={type}
```
参数列表：

1. auth_key：验证key，内测时使用
2. account_id：psn 数字 id，具体获取方法见：xxx
3. npsso：psn 账号 npsso，具体获取方法见：xxx
4. token：psn Token，具体获取方法见：xxx
5. type：psn 软件类型，game or app
返回值示例：

```json
{
    "code":200,
    "data":[
       {
            "concept": {
                "id": 228785,
                "titleIds": [],
                "name": "Monster Hunter: World",
                "genres": [
                    "ACTION",
                    "ROLE_PLAYING_GAMES"
                ],
                "subGenres": [
                    "N/A",
                    "N/A"
                ]
            },
            "playDuration": "PT2H21M26S",
            "firstPlayedDateTime": "2021-06-06T09:44:33.660Z",
            "lastPlayedDateTime": "2021-06-06T12:06:54.270Z",
            "playCount": 0,
            "category": "ps4_game",
            "localizedImageUrl": "https://image.api.playstation.com/gs2-sec/appkgo/prod/CUSA07708_00/4/i_f67bf14d3e83d266c618db72087c180869334fbbbc1b972936848e2489190681/i/icon0.png",
            "imageUrl": "https://image.api.playstation.com/gs2-sec/appkgo/prod/CUSA07708_00/4/i_f67bf14d3e83d266c618db72087c180869334fbbbc1b972936848e2489190681/i/icon0.png",
            "localizedName": "Monster Hunter: World",
            "name": "Monster Hunter: World",
            "titleId": "CUSA09554_00",
            "service": "ps_plus",
            "stats": [],
            "media": {
                "audios": [],
                "images": [
                    {
                        "url": "https://image.api.playstation.com/vulcan/ap/rnd/202009/2401/VNrDbEzvzMPPDj5S98kCC7AQ.png",
                        "type": "GAMEHUB_COVER_ART"
                    },
                    {
                        "url": "https://image.api.playstation.com/vulcan/ap/rnd/202009/2306/wezvEC1tdfMNQX7IQ5zy3QrM.png",
                        "type": "LOGO"
                    },
                    {
                        "url": "https://image.api.playstation.com/gs2-sec/appkgo/prod/CUSA07708_00/4/i_f67bf14d3e83d266c618db72087c180869334fbbbc1b972936848e2489190681/i/icon0.png",
                        "type": "MASTER"
                    }
                ],
                "videos": []
            }
        },
    ],
    "error":"",
    "error_description":""
}
```
返回值字段：

1. code：请求结果，20x：一切正常，401 auth_key 无效，其他：psn 数据获取失败，详细内容见 error 及 error_description
