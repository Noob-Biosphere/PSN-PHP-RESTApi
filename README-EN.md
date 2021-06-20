# PSN Token/GameList Rest API
A simple PSN Rest API Wrapper,you can use it to get psn token/gamelist .

## Based On Those Project
- [sofical/restphp](https://github.com/sofical/restphp)
- [Ragowit/PsnApiWrapperNet](https://github.com/Ragowit/PsnApiWrapperNet)
- [Tustin/psn-php](https://github.com/Tustin/psn-php/)

## Useage
1. git clone
2. set up server rewrite rules (eg:nginx or apache)
3. in your browser,visit `https://yourdomain/build.php` to build restphp refs
4. all done! now you can use those rest api(eg https://yourdomain/psn/token) to do something

## API
The base url is "https://yourdomain/psn"

### /token
Get a new token,you need that token to get gamelist.

#### Type
Get

#### Params
1. `auth_key` : a simple string,you can use it to proect your api (may need modify `CheckAuthKey` function)   
2. `npsso` : psn npsso , when you login psn account in your browser,visit `https://yourdomain/psn/npsso` to get your npsso.
3. `client_id` : your client_id , you can get it in the address bar when you are trying to login psn in your browser.

#### Return
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
Code 200 means everything is ok,other code means someting wrong , check error and error_des see what happen.

Token will be expired 1 hour later,you can save it and reflush it every half hour.

### /GameList
Get a user's gamelist.

#### Type
Get

#### Params
1. `auth_key` : same as /token
2. `npsso` : same as /token
3. `client_id` : same as /token
4. `account_id` : psn account id(eg:6855748483997255481), **not** the online id
5. `token` : access_token
6. `offset` : default `0`, use for pagination
7. `limit` : default `10` , use for pagination
8. `categories` : default `ps4_game,ps5_native_game`,other value:`ps4_nongame_mini_app`、`ps5_native_media_app`

#### Return

Json

- Success

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

- Failed

```json
{
  "code": 3239941,
  "data": null,
  "error": "Unauthorized",
  "error_description": "Invalid token"
}
```

Code 200: success,other code means failed.