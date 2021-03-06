define({ "api": [
  {
    "type": "get",
    "url": "/admin/bp",
    "title": "获取bp数据",
    "group": "admin/bp",
    "name": "getBP",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏Id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏,kog或lol</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": {\n     \"left\": {\n         \"ban\": [123, 111], // ban英雄id\n         \"banHero\": [\"姜子牙\", \"关羽\"], // ban英雄名字\n         \"pick\": [1], // pick英雄id\n         \"pickHero\": [\"白起\"], // pick英雄的名字\n         \"skill\": [1243,43434,3434], // pick英雄对应的英雄技能\n     },\n     \"right\": RIGHT,\n     \"scene\": \"BP状态值\"\n }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/bp.js",
    "groupTitle": "admin/bp",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/bp"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/bp/scene",
    "title": "获取bp状态码",
    "group": "admin/bp",
    "name": "getBPScene",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [\n     {\n         \"label\": \"BP状态码\",\n         \"value\": \"scene\",\n         \"children\": [\n             {\n                 \"label\": \"状态名\",\n                 \"value\": \"状态值\"\n             }\n         ]\n     }\n ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/bp.js",
    "groupTitle": "admin/bp",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/bp/scene"
      }
    ]
  },
  {
    "type": "post",
    "url": "/admin/bp",
    "title": "设置bp数据",
    "group": "admin/bp",
    "name": "setBP",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏，kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>修改的数据</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "r",
            "description": "<p>是否强制覆盖ocr生成的数据。 y -- 覆盖， n -- 不覆盖（默认）</p>"
          }
        ]
      }
    },
    "examples": [
      {
        "title": "说明",
        "content": "参数data传回getBP中的data数据类型",
        "type": "js"
      }
    ],
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/bp.js",
    "groupTitle": "admin/bp",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/bp"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/buff",
    "title": "获取buff数据",
    "group": "admin/buff",
    "name": "getBuff",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏，kog或lol</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n        \"status\": \"success\",\n        \"msg\": \"\",\n        \"data\": {\n            \"left_baojun\": \"2\",\n            \"right_baojun\": \"2\",\n            \"left_darkbaojun\": \"3\",\n            \"right_darkbaojun\": \"3\",\n            \"left_zhuzai\": \"4\",\n            \"right_zhuzai\": \"4\"\n        }\n    }",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/buff.js",
    "groupTitle": "admin/buff",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/buff"
      }
    ]
  },
  {
    "type": "post",
    "url": "/admin/buff",
    "title": "修改buff数据",
    "group": "admin/buff",
    "name": "setBuff",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏，kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": ""
          }
        ]
      }
    },
    "examples": [
      {
        "title": "说明",
        "content": "请求体中的data, 是包含以下1个或以上键值的对象\n{\n \"left_baojun\": Number, // 左队暴君\n \"right_baojun\": Number, // 右队暴君\n \"left_darkbaojun\": Number, // 左队黑暗暴君\n \"right_darkbaojun\": Number, // 右队黑暗暴君\n \"left_zhuzai\": Number, // 左队主宰\n \"right_zhuzai\": Number // 右队主宰\n}",
        "type": "js"
      }
    ],
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/buff.js",
    "groupTitle": "admin/buff",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/buff"
      }
    ]
  },
  {
    "type": "post",
    "url": "/admin/event/del/line",
    "title": "删除事件拐点",
    "group": "admin/event",
    "name": "delEventGoldLine",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Content-Type",
            "defaultValue": "application/json",
            "description": ""
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "time",
            "description": "<p>事件点</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/event.js",
    "groupTitle": "admin/event",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/event/del/line"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/event/list",
    "title": "获取事件类型列表",
    "group": "admin/event",
    "name": "getEventList",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [\n     {\n         \"label\": \"王者荣耀\",\n         \"value\": \"kog\",\n         \"children\": [\n             {\n                 \"label\": \"事件名\",\n                 \"value\": \"事件值\"\n             }\n         ]\n     }\n ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/event.js",
    "groupTitle": "admin/event",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/event/list"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/event/line",
    "title": "获取时间的经济曲线",
    "group": "admin/event",
    "name": "getGoldsLine",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏名，kog或lol</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [\n        {\n            \"left\": [ // 每个人的经济\n                460,\n                461,\n                490,\n                499,\n                407\n            ],\n            \"right\": [\n                344,\n                368,\n                533,\n                444,\n                344\n            ],\n            \"time\": 53, // 时间，单位s\n            \"left_total\": 2317,\n            \"right_total\": 2033,\n            \"isInflexion\": false // 是否是拐点\n        }\n ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/event.js",
    "groupTitle": "admin/event",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/event/line"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/event/near",
    "title": "获取时间点附近的事件",
    "group": "admin/event",
    "name": "getNearEvents",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>比赛id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏,kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "time",
            "description": "<p>时间，单位s</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [\n     {\n         \"p1\": -1,\n         \"p2\": 2,\n         \"time\": 43, // 单位s\n         \"type\": \"事件值\",\n         \"timestamp\": \"生成的时间戳\",\n         \"scene\": \"\"\n     }\n ],\n \"isInflexion\": false, // 是否是拐点\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/event.js",
    "groupTitle": "admin/event",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/event/near"
      }
    ]
  },
  {
    "type": "post",
    "url": "/admin/event/set/line",
    "title": "设置拐点事件",
    "group": "admin/event",
    "name": "setEventGoldLine",
    "header": {
      "fields": {
        "Header": [
          {
            "group": "Header",
            "type": "String",
            "optional": false,
            "field": "Content-Type",
            "defaultValue": "application/json",
            "description": ""
          }
        ]
      }
    },
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Array",
            "optional": false,
            "field": "data",
            "description": "<p>回传设置的事件数组</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏，kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "time",
            "description": "<p>事件事件点，单位s</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/event.js",
    "groupTitle": "admin/event",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/event/set/line"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/home/games",
    "title": "获取游戏的每局id",
    "group": "admin/home",
    "name": "getGameList",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏，kog或lol</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [\"123\", \"344\"]\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/home.js",
    "groupTitle": "admin/home",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/home/games"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/home/players",
    "title": "获取队员列表",
    "group": "admin/home",
    "name": "getPlayers",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [\n     {\n         \"label\": \"王者荣耀\",\n         \"value\": \"kog\",\n         \"children\": [\n             {\n                 \"label\": \"队伍名\",\n                 \"value\": \"队伍id\",\n                 \"children\": [\n                     {\n                         \"label\": \"队员名\",\n                         \"value\": \"队员id\"\n                     }\n                 ]\n             }\n         ]\n     }\n ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/home.js",
    "groupTitle": "admin/home",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/home/players"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/home/skill",
    "title": "获取英雄技能",
    "group": "admin/home",
    "name": "getSkill",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏，kog或lol</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [{summoner_id: \"技能id\", summoner_name: \"技能名\"}]\n}\n\n// kog技能图片地址http://game.gtimg.cn/images/yxzj/img201606/summoner/${id}.jpg\n// lol技能图片地址http://ossweb-img.qq.com/images/lol/img/spell/${id}.png",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/home.js",
    "groupTitle": "admin/home",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/home/skill"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/players/baseInfo",
    "title": "获取当局基本对位信息",
    "group": "admin/players",
    "name": "getBaseInfo",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏名，kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": {\n     \"left\": {\n         \"players\": [\n         {\n             \"heroId\": \"英雄Id\",\n             \"heroName\": \"英雄名\"\n         }\n     ]\n     },\n     \"right\": RIGHT,\n     \"__update_time__\": \"更新的时间戳\"\n }\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/players.js",
    "groupTitle": "admin/players",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/players/baseInfo"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/players/",
    "title": "获取游戏的选座情况",
    "group": "admin/players",
    "name": "getPlayersById",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏名，lol或kog</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": {\n     \"left\": [\n         { \"name\": \"\", \"playerId\": \"\", teamId: \"\" }\n     ],\n     \"right\": [\n         RIGHT\n     ]\n }\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/players.js",
    "groupTitle": "admin/players",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/players/"
      }
    ]
  },
  {
    "type": "post",
    "url": "/admin/players/",
    "title": "设置游戏的选座情况",
    "group": "admin/players",
    "name": "setPlayers",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏名，lol或kog</p>"
          },
          {
            "group": "Parameter",
            "type": "Object",
            "optional": false,
            "field": "data",
            "description": "<p>选座情况</p>"
          }
        ]
      }
    },
    "examples": [
      {
        "title": "测试请求体",
        "content": "{\n \"id\": 1,\n \"game\": \"kog\",\n \"data\": {\n     \"left\": [\n         { \"name\": \"name\", \"playerId\": \"1\", \"teamId\": \"4375\" }\n     ],\n     \"right\": [\n         { \"name\": \"name\", \"playerId\": \"1\", \"teamId\": \"4375\" }\n     ]\n }\n}",
        "type": "json"
      }
    ],
    "success": {
      "examples": [
        {
          "title": "SuccessResponse:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "ErrorResponse:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/players.js",
    "groupTitle": "admin/players",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/players/"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/process/del",
    "title": "删除实例进程",
    "group": "admin/process",
    "name": "delProcess",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏,kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/process.js",
    "groupTitle": "admin/process",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/process/del"
      }
    ]
  },
  {
    "type": "post",
    "url": "/admin/process",
    "title": "开启新的进程任务",
    "group": "admin/process",
    "name": "generateProcess",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "leftId",
            "description": "<p>左队id</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "rightId",
            "description": "<p>右队id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏，kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "input",
            "description": "<p>rtmp地址</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/process.js",
    "groupTitle": "admin/process",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/process"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/process/list",
    "title": "获取实例列表",
    "group": "admin/process",
    "name": "getProcess",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [\n     { \"game\": \"kog\", \"id\": 1, \"status\": \"idel\", \"pid\": \"23234\", startTime: 1529496441 } // status有：idel -- 等待，running -- 正在运行中，exit -- 已关闭，error -- 发生错误\n ]\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/process.js",
    "groupTitle": "admin/process",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/process/list"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/process/stop",
    "title": "停止实例进程",
    "group": "admin/process",
    "name": "stopProcess",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏,kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/process.js",
    "groupTitle": "admin/process",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/process/stop"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/sync",
    "title": "开启同步进程",
    "group": "admin/sync",
    "name": "remoteSync",
    "examples": [
      {
        "title": "说明",
        "content": "这个接口使用Server Send Event, 如果接收到event为end，请务必关闭连接。",
        "type": "js"
      }
    ],
    "version": "0.0.0",
    "filename": "controller/admin/sync.js",
    "groupTitle": "admin/sync",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/sync"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/sync/backup",
    "title": "开启备份进程",
    "group": "admin/sync",
    "name": "startBackUp",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"开启备份进程成功，pid为${backChild.pid}\"\n}",
          "type": "json"
        },
        {
          "title": "Fail-Response:",
          "content": "{\n \"status\": \"fail\",\n \"msg\": \"备份进程已开启\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/sync.js",
    "groupTitle": "admin/sync",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/sync/backup"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/sync/backup/status",
    "title": "查看备份进程的状态",
    "group": "admin/sync",
    "name": "statusBackup",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"备份进程正在运行中，pid为${backChild.pid}, 上一次备份时间为${new Date(t * 1000).toString()}\"\n}",
          "type": "json"
        },
        {
          "title": "Fail-Response:",
          "content": "{\n \"status\": \"fail\",\n \"msg\": \"备份进程未开启, 上一次备份时间为${new Date(t * 1000).toString()}\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status: \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/sync.js",
    "groupTitle": "admin/sync",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/sync/backup/status"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/sync/backup/stop",
    "title": "关闭备份进程",
    "group": "admin/sync",
    "name": "stopBackup",
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"关闭备份进程成功\"\n}",
          "type": "json"
        },
        {
          "title": "Fail-Response:",
          "content": "{\n \"status\": \"fail\",\n \"msg\": \"备份进程未开启\"\n}",
          "type": "json"
        }
      ]
    },
    "error": {
      "examples": [
        {
          "title": "Error-Response:",
          "content": "{\n \"status\": \"error\",\n \"msg\": \"error msg\"\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/admin/sync.js",
    "groupTitle": "admin/sync",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/sync/backup/stop"
      }
    ]
  },
  {
    "type": "get",
    "url": "/admin/sync/baseInfo",
    "title": "同步baseInfo",
    "group": "admin/sync",
    "name": "syncBaseInfo",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          },
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "description": "<p>游戏</p>"
          }
        ]
      }
    },
    "version": "0.0.0",
    "filename": "controller/admin/sync.js",
    "groupTitle": "admin/sync",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/admin/sync/baseInfo"
      }
    ]
  },
  {
    "type": "get",
    "url": "/api/event/list",
    "title": "获取事件列表",
    "group": "api",
    "name": "getEventLine",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "defaultValue": "kog",
            "description": "<p>游戏,kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>游戏id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [\n {\n \"p1\": {\"heroId\": \"ID\", \"heroName\": \"heroName\", \"name\": \"队员名\", \"teamId\": \"队伍id\", \"playerId\": \"队员id\"},\n \"p2\": null,\n \"timestamp\": \"时间戳\",\n \"time\": \"时间发生时间,单位s\",\n \"type\": \"事件类型值\"\n }\n]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/api/event.js",
    "groupTitle": "api",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/api/event/list"
      }
    ]
  },
  {
    "type": "get",
    "url": "/api/gradient/event",
    "title": "获取拐点事件列表",
    "group": "api",
    "name": "getGradientEvent",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "game",
            "defaultValue": "kog",
            "description": "<p>游戏,kog或lol</p>"
          },
          {
            "group": "Parameter",
            "type": "Number",
            "optional": false,
            "field": "id",
            "description": "<p>,游戏id</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n \"status\": \"success\",\n \"msg\": \"\",\n \"data\": [\n {\n \"p1\": {\"heroId\": \"ID\", \"heroName\": \"heroName\", \"name\": \"队员名\", \"teamId\": \"队伍id\", \"playerId\": \"队员id\"},\n \"p2\": null,\n \"timestamp\": \"时间戳\",\n \"time\": \"时间发生时间,单位s\",\n \"type\": \"事件类型值\"\n }\n ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "controller/api/event.js",
    "groupTitle": "api",
    "sampleRequest": [
      {
        "url": "http://192.168.11.3:3004/api/gradient/event"
      }
    ]
  }
] });
