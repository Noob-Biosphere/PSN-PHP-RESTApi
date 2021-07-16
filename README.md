# PSN Token/GameList Rest API
一个简单的 Rest PSN API 代理，可用于返回 PSN token 以及游戏列表。

## 引用资源
- [sofical/restphp](https://github.com/sofical/restphp)
- [Ragowit/PsnApiWrapperNet](https://github.com/Ragowit/PsnApiWrapperNet)
- [Tustin/psn-php](https://github.com/Tustin/psn-php/)

## 使用方法
1. git clone
2. 设置服务器程序 rewrite 规则，将访问重写到 index.php 上。
3. 访问 `https://yourdomain/build.php` 生成 Rest 框架所需的文件。
4. 完成! 现在可以使用 REST (例如 https://yourdomain/psn/token) 获取数据了。

## API
API 前缀统一为 "https://yourdomain/psn"

### /token
获取一个新 Token，用于获取游戏列表时使用。

#### 请求类型
Get

#### 参数
1. `auth_key` : 一个字符串，用于防止 api 被滥用(自己改 `CheckAuthKey` 方法，用上这个字符串)   
2. `npsso` : psn npsso , 在浏览器访问 psn 官网，登录 psn 后，访问 `https://yourdomain/psn/npsso` 获取 npsso
3. `client_id` : 设备 id , 浏览器访问 psn 官网，登录 psn 时，浏览器地址栏上有 client_id，该项不能随机生成。

#### 返回值
Json Object

```json
{
  "code": 200,
  "access_token": "00000000-0000-0000-0000-000000000000",
  "expires": 3599,
  "error": "",
  "error_description": ""
}
```
Code 200 一切正常，其他 Code 代表异常，需要参考 error 与 error_description

Token 3600 秒过期（expiress），你可以记录下 Token 失效的时间，并在失效前拿一个新 Token，以便减少对服务器的访问次数。

<!-- ### /GameList(挂了)
获取游戏列表

#### 请求类型
Get

#### 参数列表
1. `auth_key` : 与 /token 一样
2. `npsso` : 与 /token 一样
3. `client_id` : 与 /token 一样
4. `account_id` : psn 账号 id(例如:6855748483997255481), **不是** 在线 id，更不是昵称，获取方法自行百度。
5. `token` : 从 /token 接口拿到的 token
6. `offset` : 默认 `0`, 分页时使用
7. `limit` : 默认 `10` , 分页时使用
8. `categories` : 分类，默认 `ps4_game,ps5_native_game`，其他值:`ps4_nongame_mini_app`、`ps5_native_media_app`

#### 返回值

Json

- 成功

```json
{
  "code": 200,
  "data": {
    "titles": [
      {
        "concept": {
          "id": 234657,
          "titleIds": [],
          "name": "STAR WARS™: Squadrons",
          "genres": [
            "ACTION"
          ],
          "subGenres": [
            "N/A"
          ]
        },
        "playDuration": "PT48M48S",
        "firstPlayedDateTime": "2021-06-09T16:13:11.390Z",
        "lastPlayedDateTime": "2021-06-09T17:09:30.470Z",
        "playCount": 0,
        "category": "ps4_game",
        "localizedImageUrl": "https://image.api.playstation.com/vulcan/ap/rnd/202006/1219/aP7aCfJPhs5O0QfzeaoxzrjG.png",
        "imageUrl": "https://image.api.playstation.com/vulcan/ap/rnd/202006/1219/aP7aCfJPhs5O0QfzeaoxzrjG.png",
        "localizedName": "STAR WARS™: Squadrons",
        "name": "STAR WARS™: Squadrons",
        "titleId": "CUSA15080_00",
        "service": "ps_plus",
        "stats": [],
        "media": {
          "audios": [],
          "images": [
            {
              "url": "https://image.api.playstation.com/vulcan/img/rnd/202011/0204/Phl0wzhvugJun7xJROrnyotT.png",
              "type": "GAMEHUB_COVER_ART"
            }
          ],
          "videos": []
        }
      }
    ],
    "nextOffset": 1,
    "previousOffset": null,
    "totalItemCount": 28
  },
  "error": "",
  "error_description": ""
}
```

- 失败

```json
{
  "code": 3239941,
  "data": null,
  "error": "Unauthorized",
  "error_description": "Invalid token"
}
```

Code 200 为成功，其余为失败。data 字段为索尼返回的数据或 null，需要根据 code 酌情取值。 -->

### /Trophy

获取奖杯列表（用来替代获取游戏列表）

#### 请求类型
Get

#### 参数类型
1. `auth_key` : 与 /token 一样
2. `account_id` : psn 账号 id(例如:6855748483997255481), **不是** 在线 id，更不是昵称，获取方法自行百度。
3. `token` : 从 /token 接口拿到的 token
4. `offset` : 默认 `0`, 分页时使用
5. `limit` : 默认 `10` , 分页时使用

### 返回值
Json

成功

```json
{
  "code": 200,
  "data": {
    "trophyTitles": [
      {
        "npServiceName": "trophy",
        "npCommunicationId": "NPWR07942_00",
        "trophySetVersion": "01.01",
        "trophyTitleName": "Ratchet & Clank™",
        "trophyTitleDetail": "Trophy set for Ratchet & Clank™.",
        "trophyTitleIconUrl": "https://image.api.playstation.com/trophy/np/NPWR07942_00_006F781DB9EE3B1A96EB9472B006DA21899A916D8F/0A529D9F4EA9446B6946C0CDC64C5DD853DC79D8.PNG",
        "trophyTitlePlatform": "PS4",
        "hasTrophyGroups": false,
        "definedTrophies": {
          "bronze": 30,
          "silver": 14,
          "gold": 2,
          "platinum": 1
        },
        "progress": 12,
        "earnedTrophies": {
          "bronze": 9,
          "silver": 0,
          "gold": 0,
          "platinum": 0
        },
        "hiddenFlag": false,
        "lastUpdatedDateTime": "2020-11-16T12:06:19Z"
      }
    ],
    "nextOffset": 11,
    "previousOffset": 9,
    "totalItemCount": 47
  },
  "error": "",
  "error_description": ""
}
```

失败

```json
{
  "code": 2241025,
  "data": "{\"error\":{\"referenceId\":\"00000000-0000-0000-0000-000000000000\",\"code\":2241025,\"message\":\"Invalid token\"}}",
  "error": "Get trophy failed",
  "error_description": "Invalid token"
}
```