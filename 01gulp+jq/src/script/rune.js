define(function () {
    return [
        {
            "name": "坚决",
            "slogan": "永生",
            "description": "耐久和控制",
            "bonuses": [
                {
                    "name": "坚决+精密",
                    "value": "+65生命值<br>+9%攻击速度"
                },
                {
                    "name": "坚决+主宰",
                    "value": "+65生命值<br>+5攻击力或+9法术强度，<font color='#48C4B7'>自动适应</font>"
                },
                {
                    "name": "坚决+巫术",
                    "value": "+65生命值<br>+6攻击力或+10法术强度，<font color='#48C4B7'>自动适应</font>"
                },
                {
                    "name": "坚决+启迪",
                    "value": "+130生命值"
                }
            ],
            "slots": [
                {
                    "runes": [
                        {
                            "name": "不灭之握",
                            "longDescription": "在战斗中每过4秒，你对一个英雄发起的下一次普攻将会：<li>造成相当于你4%最大生命值的魔法伤害</li><li>治疗你2%的最大生命值</li><li>你的生命值永久提升5</li><br><rules><i>远程英雄：</i>伤害、治疗效果和提升的永久生命值减少40%。</rules><br>",
                            "shortDescription": "在战斗中每过4秒，你对一个英雄发起的下一次普攻将会造成额外魔法伤害、治疗你、并永久提升你的生命值。",
                            "runeId": 8437
                        },
                        {
                            "name": "余震",
                            "longDescription": "在定身住一名敌方英雄后，使你的当前护甲和魔法抗性提升70 + 0%，持续2.5秒。随后，在2.5秒后对附近的敌人造成魔法伤害。<br><br>伤害值：10 - 120 (+你3.0%的最大生命值) (+15%额外攻击力) (+10%法术强度)<br>冷却时间：35秒",
                            "shortDescription": "在你<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Immobilize\">定身</lol-uikit-tooltipped-keyword>一个敌方英雄后提供防御属性，稍后会在你的周围造成爆发性的<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_AdaptiveDmg'>自适应伤害</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8439
                        },
                        {
                            "name": "守护者",
                            "longDescription": "<i>守护</i>距离你175码内的友军，以及被你选为技能目标的友军2.5秒。当<i>守护</i>持续时，如果你和该友军承受伤害，那么你们两个都会获得一层护盾且被加速1.5秒。<br><br>冷却时间：<scaleLevel>70 - 40</scaleLevel>秒<br>护盾生命值：<scaleLevel>70 - 150</scaleLevel> + <scaleAP>0.25%</scaleAP>法术强度 + <scalehealth>12%</scalehealth>额外生命值。<br>加速效果：+20%移动速度。",
                            "shortDescription": "守护你选作技能目标的友军和那些距离你非常近的友军们。如果你和一个被守护的友军即将承受伤害，那么你们两个都会获得加速效果和一层护盾。",
                            "runeId": 8465
                        }
                    ],
                    "name": "基石"
                },
                {
                    "runes": [
                        {
                            "name": "爆破",
                            "longDescription": "当位于防御塔600码内时，在4秒里持续充能一次针对防御塔的强力攻击。充能攻击造成125(+你30%最大生命值)额外伤害。<br><br>冷却时间：45秒",
                            "shortDescription": "当位于防御塔附近时，持续充能一次针对防御塔的强力攻击。",
                            "runeId": 8446
                        },
                        {
                            "name": "生命源泉",
                            "longDescription": "对一名敌方英雄施加移动受损效果会将其标记4秒。<br><br>友方英雄在攻击被标记的敌人时，会在2秒里共获得5+你1%最大生命值的治疗效果。",
                            "shortDescription": "对一名敌方英雄施加<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_ImpairMov'>移动受损</lol-uikit-tooltipped-keyword>效果会将其标记。你和友方英雄在攻击被标记的英雄时会获得治疗效果。",
                            "runeId": 8463
                        },
                        {
                            "name": "骸骨镀层",
                            "longDescription": "在承受来自敌方英雄的伤害后，来自敌方的下3个技能或攻击对你造成的伤害减少20-50。<br><br>持续时间：3秒<br>冷却时间：45秒",
                            "shortDescription": "在承受来自敌方英雄的伤害后，来自敌方的下3个技能或攻击对你造成的伤害减少20-50。<br><br>持续时间：3秒<br>冷却时间：45秒",
                            "runeId": 8473
                        }
                    ],
                    "name": "蛮力"
                },
                {
                    "runes": [
                        {
                            "name": "调节",
                            "longDescription": "在10分钟时获得+8护甲和+8魔抗，并使你的护甲和魔抗提升5%。",
                            "shortDescription": "在10分钟时获得+8护甲和+8魔抗，并使你的护甲和魔抗提升5%。",
                            "runeId": 8429
                        },
                        {
                            "name": "复苏之风",
                            "longDescription": "在你受到来自一名敌方英雄伤害之后，你会在10秒里持续回复生命值，总额相当于你4%已损失生命值 +6。",
                            "shortDescription": "在受到来自一名敌方英雄的伤害后，会持续治疗你一些已损失生命值。",
                            "runeId": 8444
                        },
                        {
                            "name": "蛹化",
                            "longDescription": "开始游戏时带有60额外生命值。在参与击杀4次后，消耗掉该生命值加成来获得一个<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应的</font></lol-uikit-tooltipped-keyword>加成，9攻击力或15法术强度。",
                            "shortDescription": "开始游戏时带有60额外生命值。在参与击杀4次后，消耗掉该生命值加成来获得一个<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应的</font></lol-uikit-tooltipped-keyword>加成，9攻击力或15法术强度。",
                            "runeId": 8472
                        }
                    ],
                    "name": "抵抗"
                },
                {
                    "runes": [
                        {
                            "name": "过度生长",
                            "longDescription": "你附近每有8个野怪或小兵死亡，就会使你永久获得0.2%最大生命值。",
                            "shortDescription": "你附近每有小兵或野怪死亡时，提供永久最大生命值加成。",
                            "runeId": 8451
                        },
                        {
                            "name": "复苏",
                            "longDescription": "你施放或获得的治疗效果和护盾效果会增强5%，并且如果目标的生命值低于40%，则此数值会提升额外的10%。",
                            "shortDescription": "你施放或受到的治疗效果和护盾效果会增强5%，并且如果目标的生命值较低，则此数值会提升额外的10%。",
                            "runeId": 8453
                        },
                        {
                            "name": "坚定",
                            "longDescription": "在施放一个召唤师技能后，获得15%韧性和15%减速抵抗，持续10秒。此外，你每有一个尚未冷却完毕的召唤师技能，你的韧性和减速抵抗就会提升10%。",
                            "shortDescription": "在施放一个召唤师技能后，立刻获得幅度更大的韧性和减速抵抗，持续一段不短的时间。此外，你每有一个尚未冷却完毕的召唤师技能，你的韧性和减速抵抗就会获得提升。",
                            "runeId": 8242
                        }
                    ],
                    "name": "生机"
                }
            ]
        },
        {
            "name": "主宰",
            "slogan": "猎杀并消灭猎物",
            "description": "爆发伤害并前往目标",
            "bonuses": [
                {
                    "name": "主宰+启迪",
                    "value": "+11攻击力或+18法术强度，<font color='#48C4B7'>自动适应</font>"
                },
                {
                    "name": "主宰+坚决",
                    "value": "+5攻击力或+9法术强度，<font color='#48C4B7'>自动适应</font><br>+65 生命值"
                },
                {
                    "name": "主宰+巫术",
                    "value": "+11攻击力或+18法术强度，<font color='#48C4B7'>自动适应</font>"
                },
                {
                    "name": "主宰+精密",
                    "value": "+8攻击力或+13法术强度，<font color='#48C4B7'>自动适应</font><br>+5.5%攻击速度"
                }
            ],
            "slots": [
                {
                    "runes": [
                        {
                            "name": "电刑",
                            "longDescription": "在3秒内用3个<b>独立的</b>攻击或技能命中一位英雄时，会造成额外的<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_AdaptiveDmg'><font color='#48C4B7'>自适应伤害</font></lol-uikit-tooltipped-keyword>。<br><br>伤害值：50 - 220 (+0.5额外攻击力, +0.3法术强度)。<br><br>冷却时间：50 - 25秒<br><br><hr></hr><i>“我们曾称呼他们为“雷霆领主”，是因为他们的闪电招来了灾祸。”</i>",
                            "shortDescription": "在3秒内用3个<b>独立的</b>攻击或技能命中一位英雄时，会造成额外的<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_AdaptiveDmg'>自适应伤害</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8112
                        },
                        {
                            "name": "掠食者",
                            "longDescription": "为你的鞋子附魔上主动效果“<font color='#c60300'>掠食者</font>。”<br><br>在非战斗状态下引导1.5秒，以获取45%移动速度加成，持续15秒。伤害型的攻击或技能会终止这个效果，并造成60 - 180 (+<scaleAD>0.4</scaleAD>额外攻击力)(+<scaleAP>0.25</scaleAP>法术强度)的额外<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_AdaptiveDmg'><font color='#48C4B7'>自适应伤害</font></lol-uikit-tooltipped-keyword>。<br><br>冷却时间：180-120秒。开始游戏时处于冷却阶段。在引导时被打断时会返还50%冷却时间。",
                            "shortDescription": "为你的鞋子添加一个主动效果，此效果可提供一个巨量移动速度加成，并使你的下次攻击或技能造成额外的<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_AdaptiveDmg'>自适应伤害</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8124
                        },
                        {
                            "name": "黑暗收割",
                            "longDescription": "英雄、大型小兵、大型野怪在死亡时会掉落灵魂精华。在收集一个灵魂时，你将变成<font color='#c60300'>灵魂充能</font>状态。你的下次对英雄或建筑物发起的攻击将消耗掉<font color='#c60300'>灵魂充能</font>状态，以造成额外的<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_AdaptiveDmg'><font color='#48C4B7'>自适应伤害</font></lol-uikit-tooltipped-keyword>。<br><font color='#c60300'>灵魂充能</font>状态持续20秒，可在收集150灵魂精华后提升至300秒。<br>额外伤害：40 - 80 (+<scaleAD>0.25额外攻击力</scaleAD>) (+<scaleAP>0.2法术强度</scaleAP>) + 已收集的灵魂精华数。<br><rules>英雄的灵魂：6个灵魂精华。<br>野怪的灵魂：2个灵魂精华。<br>小兵的灵魂：4个灵魂精华。</rules>",
                            "shortDescription": "英雄、大型小兵、大型野怪会在死亡时掉落灵魂精华。触碰一个灵魂可拾取它，并使你的下次攻击造成基于已收集灵魂精华数的额外<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_AdaptiveDmg'>自适应伤害</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8128
                        }
                    ],
                    "name": "基石"
                },
                {
                    "runes": [
                        {
                            "name": "恶意中伤",
                            "longDescription": "对<b>移动或行动受损</b>的敌人造成12~30额外真实伤害(基于等级)。<br><br>冷却时间：4秒<br><rules>会在移动受损之后触发伤害。</rules>",
                            "shortDescription": "对<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_ImpairAct\">移动或行动受损的</lol-uikit-tooltipped-keyword>敌人造成额外真实伤害。",
                            "runeId": 8126
                        },
                        {
                            "name": "血之滋味",
                            "longDescription": "在你伤害一名敌方英雄时为你提供治疗效果。<br>治疗效果：18-35 (+0.2额外攻击力，+0.1法术强度)生命值(基于等级)<br><br>冷却时间：20秒",
                            "shortDescription": "在你伤害一名敌方英雄时治疗自身。",
                            "runeId": 8139
                        },
                        {
                            "name": "猛然冲击",
                            "longDescription": "在离开潜行状态或使用一次突进、跃击、闪烁或传送效果，对英雄造成任何伤害后，你都会获得10穿甲和8法术穿透，持续5秒。<br><br>冷却时间：4秒",
                            "shortDescription": "在使用一次突进、跃击、闪烁、传送效果或离开潜行状态后获得穿甲和法术穿透。",
                            "runeId": 8143
                        }
                    ],
                    "name": "预谋"
                },
                {
                    "runes": [
                        {
                            "name": "僵尸守卫",
                            "longDescription": "在击杀一个守卫后，该守卫的位置上会升起一个友方的僵尸守卫。此外，当你的守卫过期时，它们也会复生为僵尸守卫。<br><br>僵尸守卫不是隐形的，持续60 -180秒并且不会算在你的守卫限制内。",
                            "shortDescription": "在击杀一个敌方守卫后，该守卫的位置上会升起一个友方的僵尸守卫。当该守卫的持续时间耗尽后，其仍然会复活为一个僵尸守卫。",
                            "runeId": 8136
                        },
                        {
                            "name": "幽灵魄罗",
                            "longDescription": "进入一个草丛以在短暂的引导后召唤一个魄罗。魄罗将会留下来，以提供视野给你，直到你召唤出一个新的魄罗为止。<br><br>如果敌人进入了一个已有魄罗的草丛，那么魄罗就会被吓跑，并使 幽灵魄罗 进入一个为期3秒的冷却时间。<br><br>如果你受到了伤害，那么魄罗引导就会被打断。",
                            "shortDescription": "当你进入草丛时，一个魄罗会出现。它会呆在后面来为你提供视野。",
                            "runeId": 8120
                        },
                        {
                            "name": "眼球收集器",
                            "longDescription": "在参与击杀英雄和守卫时收集眼球。每个已收集的眼球都会提供一个<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应的</font></lol-uikit-tooltipped-keyword>加成，0.6攻击力或1法术强度。<br><br>你的收集会在20个眼球时被视为已完成，额外提供一个<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应的</font></lol-uikit-tooltipped-keyword>加成，6攻击力或10法术强度。<br><br>每次击杀英雄提供2个眼球，每次协助击杀英雄提供1个眼球，每次参与击杀守卫提供1个眼球",
                            "shortDescription": "在<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Takedown\">在<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Takedown'>参与击杀</lol-uikit-tooltipped-keyword>英雄和守卫时收集眼球。每个眼球都会提供永久的攻击力或法术强度，<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'>自动适应</lol-uikit-tooltipped-keyword>，并在完成收集后会提供额外加成。",
                            "runeId": 8138
                        }
                    ],
                    "name": "追踪"
                },
                {
                    "runes": [
                        {
                            "name": "贪欲猎手",
                            "longDescription": "你的技能造成的伤害值的一部分将转化为对你的治疗效果。<br>治疗百分比：2.5%+2.5%x<i>赏金猎人</i>层数。<br><br>你首次参与击杀每位独特的敌方英雄时，都会赚取一层<i>赏金猎人</i>效果。<br><br><rules><br><i>群体技能：</i> 治疗效果会降低至三分之一。</rules>",
                            "shortDescription": "<b>独特的</b><lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Takedown\">参与击杀</lol-uikit-tooltipped-keyword>会永久使来自技能的伤害提供治疗效果。",
                            "runeId": 8135
                        },
                        {
                            "name": "灵性猎手",
                            "longDescription": "提供<b>主动装备冷却缩减</b>(包含饰品效果)，数额相当于10%+6%x<i>赏金猎人</i>层数。<br><br>你首次参与击杀每位独特的敌方英雄时，都会赚取一层<i>赏金猎人</i>效果。",
                            "shortDescription": "<b>独特的</b><lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Takedown\">参与击杀</lol-uikit-tooltipped-keyword>会提供永久的主动道具<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_CDR\">冷却缩减</lol-uikit-tooltipped-keyword>(包含饰品效果)。",
                            "runeId": 8134
                        },
                        {
                            "name": "无情猎手",
                            "longDescription": "提供额外的<b>非战斗状态移动速度加成</b>，数额相当于8+8x<i>赏金猎人</i>层数。<br><br>你首次参与击杀每位独特的敌方英雄时，都会赚取一层<i>赏金猎人</i>效果。",
                            "shortDescription": "<b>独特的</b><lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Takedown\">参与击杀</lol-uikit-tooltipped-keyword>会提供永久的<b>非战斗状态<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_MS\">移动速度加成</lol-uikit-tooltipped-keyword></b>。",
                            "runeId": 8105
                        }
                    ],
                    "name": "狩猎"
                }
            ]
        },
        {
            "name": "精密",
            "slogan": "成为一个传说",
            "description": "强化攻击和持续伤害",
            "bonuses": [
                {
                    "name": "精密+巫术",
                    "value": "+9%攻击速度<br>+6攻击力或+10法术强度，<font color='#48C4B7'>自动适应</font>"
                },
                {
                    "name": "精密+坚决",
                    "value": "+9%攻击速度<br>+65生命值"
                },
                {
                    "name": "精密+启迪",
                    "value": "+18%攻击速度"
                },
                {
                    "name": "精密+主宰",
                    "value": "+9%攻击速度<br>+6攻击力或+10法术强度，<font color='#48C4B7'>自动适应</font>"
                }
            ],
            "slots": [
                {
                    "runes": [
                        {
                            "name": "强攻",
                            "longDescription": "用3次连续的普攻命中一名敌方英雄时，将造成40 - 180的额外<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_AdaptiveDmg'><font color='#48C4B7'>自适应伤害</font></lol-uikit-tooltipped-keyword>（基于等级）并使其进入易损状态，让其所受的来自任意来源的伤害提升8 - 12%，持续6秒。",
                            "shortDescription": "用3次连续的普攻命中一名敌方英雄时，将使其变为易损状态，造成额外伤害并使所受的来自任意来源的伤害变多，持续6秒。",
                            "runeId": 8005
                        },
                        {
                            "name": "致命节奏",
                            "longDescription": "在对英雄造成伤害的1.5秒后，提供30 - 80%攻击速度(基于等级)，持续3秒。你可通过不断攻击一名敌方英雄来让这个效果延长至6秒。<br><br>冷却时间：6秒<br><br>【致命节奏】允许你暂时溢出你的攻击速度上限。",
                            "shortDescription": "在伤害一名英雄1.5秒后，你会获得大幅攻击速度加成。【致命节奏】允许你暂时溢出你的攻击速度上限。",
                            "runeId": 8008
                        },
                        {
                            "name": "迅捷步法",
                            "longDescription": "攻击和移动会积攒能量层数。在100层时，你的下次攻击会充盈能量。<br><br>充盈能量的攻击会治疗你3 - 60(+0.3额外攻击力。+0.4法术强度)并提供+30%移动速度，持续1秒。<br><rules>在用在小兵身上时，治疗效果的效能为60%(远程英雄为30%)。<br>如果已命中的攻击触发了暴击，那么治疗效果会得到提升，数额为你40%的暴击伤害修正系数。</rules>",
                            "shortDescription": "攻击和移动会积攒能量层数。在100层时，你的下次攻击会治疗你并提升<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_MS'>移动速度</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8021
                        },
                        {
                            "name": "征服者",
                            "longDescription": "在处于战斗状态4秒后，你对敌方英雄发起的第一次攻击将为你提供10 - 35攻击力，基于等级，持续3秒，在此期间将你对该英雄造成伤害值的20%转化为真实伤害。<br><br><rules>仅限近战：对敌方英雄造成伤害时可刷新这个增益效果。</rules>",
                            "shortDescription": "在处于战斗状态4秒后，你对敌方英雄发起的第一次攻击将为你提供攻击力，并将你对该英雄造成伤害值中的一部分转化为真实伤害。",
                            "runeId": 8010
                        }
                    ],
                    "name": "基石"
                },
                {
                    "runes": [
                        {
                            "name": "过量治疗",
                            "longDescription": "你身上的溢出治疗效果会变成一个护盾，护盾生命值最多可达你10%总生命值+10。<br><br>护盾的增加方式为：40%的自我治疗效果溢出，或300%的来自友方的治疗效果溢出。",
                            "shortDescription": "你身上的溢出治疗效果将变成一个护盾。",
                            "runeId": 9101
                        },
                        {
                            "name": "凯旋",
                            "longDescription": "参与击杀会回复12%已损失生命值并提供额外的20金币。<br><br><hr></hr><i>“最危险的游戏带来最伟大的荣光。”<br>—诺克萨斯清算人</i>",
                            "shortDescription": "<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Takedown\">参与击杀</lol-uikit-tooltipped-keyword>会回复你15%的已损失生命值并提供额外25金币。",
                            "runeId": 9111
                        },
                        {
                            "name": "气定神闲",
                            "longDescription": "在升级或参与击杀后的7秒里，你花费的任何法力都会得到完全返还。",
                            "shortDescription": "在升级或参与击杀后的7秒里，你花费的任何法力都会得到完全返还。",
                            "runeId": 8009
                        }
                    ],
                    "name": "英武"
                },
                {
                    "runes": [
                        {
                            "name": "传说：欢欣",
                            "longDescription": "获得3%攻击速度，此外每层<i>传奇</i>效果额外提供1.5%攻击速度(最大层数：10)。<br><br>赚取<i>传奇</i>层数进度的方式为：参与英雄击杀，击杀大型野怪，击杀小兵。",
                            "shortDescription": "<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Takedown\">参与击杀</lol-uikit-tooltipped-keyword>敌人将提供永久的<b>攻击速度</b>。",
                            "runeId": 9104
                        },
                        {
                            "name": "传说：韧性",
                            "longDescription": "获得5%韧性，此外每层<i>传奇</i>效果额外提供1.5%韧性(最大层数：10)。<br><br>赚取<i>传奇</i>层数进度的方式为：参与英雄击杀，击杀大型野怪，击杀小兵。",
                            "shortDescription": "<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Takedown\">参与击杀</lol-uikit-tooltipped-keyword>敌人将提供永久的<b>韧性</b>。",
                            "runeId": 9105
                        },
                        {
                            "name": "传说：血统",
                            "longDescription": "每层<i>传奇</i>效果提供0.8%生命偷取(最大层数：10)。<br><br>赚取<i>传奇</i>层数进度的方式为：参与英雄击杀，击杀大型野怪，击杀小兵。",
                            "shortDescription": "<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Takedown\">参与击杀</lol-uikit-tooltipped-keyword>敌人将提供永久的<b>生命偷取</b>。",
                            "runeId": 9103
                        }
                    ],
                    "name": "传说"
                },
                {
                    "runes": [
                        {
                            "name": "致命一击",
                            "longDescription": "对生命值低于40%的英雄多造成7%伤害。<br><br>此外，参与击杀英雄会提供一个<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应的</font></lol-uikit-tooltipped-keyword>加成，9攻击力或15法术强度，持续10秒。",
                            "shortDescription": "对低生命值的敌方英雄造成更多伤害。",
                            "runeId": 8014
                        },
                        {
                            "name": "砍倒",
                            "longDescription": "对最大生命值比你多150以上的敌方英雄多造成4%伤害，在最大生命值差额为2000时提升到最大值10%。",
                            "shortDescription": "对最大生命值多于你的所有敌方英雄造成更多伤害。",
                            "runeId": 8017
                        },
                        {
                            "name": "坚毅不倒",
                            "longDescription": "当你的生命值低于60%时，对英雄多造成5% -11%伤害。最大伤害会在30%生命值时提供。",
                            "shortDescription": "当你的生命值较低时，你的攻击对英雄多造成伤害。",
                            "runeId": 8299
                        }
                    ],
                    "name": "战斗"
                }
            ]
        },
        {
            "name": "巫术",
            "slogan": "释放毁灭",
            "description": "强化技能和资源控制",
            "bonuses": [
                {
                    "name": "巫术+精密",
                    "value": "+8攻击力或+14法术强度，<font color='#48C4B7'>自动适应</font><br>+5.5% 攻击速度"
                },
                {
                    "name": "巫术+启迪",
                    "value": "+12攻击力或+20法术强度，<font color='#48C4B7'>自动适应</font>"
                },
                {
                    "name": "巫术+坚决",
                    "value": "+6攻击力或+10法术强度，<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应</font></lol-uikit-tooltipped-keyword><br>+65生命值"
                },
                {
                    "name": "巫术+主宰",
                    "value": "+12攻击力或+20法术强度，<font color='#48C4B7'>自动适应</font>"
                }
            ],
            "slots": [
                {
                    "runes": [
                        {
                            "name": "召唤：艾黎",
                            "longDescription": "你的攻击和技能会向目标派出艾黎，来对敌方英雄造成伤害或为友方英雄套上护盾。<br><br>伤害：15 - 40（基于等级）+<scaleAP>0.1法术强度</scaleAP>和+<scaleAD>0.15额外攻击力</scaleAD><br>护盾生命值：30 - 80（基于等级）+<scaleAP>0.25法术强度</scaleAP>和+<scaleAD>0.4额外攻击力</scaleAD>) <br><br>艾黎在回到你的位置前无法被再次派出。",
                            "shortDescription": "你的攻击和技能会向目标派出艾黎，来对敌方英雄造成伤害或为友方英雄套上护盾。",
                            "runeId": 8214
                        },
                        {
                            "name": "奥术彗星",
                            "longDescription": "用一个技能对一名英雄造成伤害时，会在其位置处召唤一颗彗星，若【奥术彗星】尚未冷却，则改为减少它的剩余冷却时间。<br><br><lol-uikit-tooltipped-keyword key='LinkTooltip_Description_AdaptiveDmg'><font color='#48C4B7'>自适应伤害</font></lol-uikit-tooltipped-keyword>：30 - 100基于等级(<scaleAP>+0.2法术强度</scaleAP>和<scaleAD>+0.35额外攻击力</scaleAD>)<br>冷却时间：20 - 8秒<br><rules><br>冷却缩减：<br>单体目标技能：20%。<br>群体目标技能：10%。<br>持续伤害技能：5%。<br></rules>",
                            "shortDescription": "用一个技能对一名英雄造成伤害时，会在其位置处召唤一颗彗星。",
                            "runeId": 8229
                        },
                        {
                            "name": "相位猛冲",
                            "longDescription": "在3秒内用3次攻击或<b>独立</b>的技能命中一名敌方英雄时，会提供15 - 40%移动速度加成和75%减速抵抗（基于等级）。<br><br>持续时间：3秒<br>冷却时间：15秒",
                            "shortDescription": "用3次<b>独立</b>的攻击或技能命中一名敌方英雄时，会提供爆发性的<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_MS'>移动速度</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8230
                        }
                    ],
                    "name": "基石"
                },
                {
                    "runes": [
                        {
                            "name": "无效化之法球",
                            "longDescription": "在你受到即将使你的生命值降至30%以下的魔法伤害时，为你提供一个魔法伤害护盾，可吸收的魔法伤害值为：40 - 120（基于等级）<scaleAP>+0.1法术强度</scaleAP>和<scaleAD>+0.15额外攻击力</scaleAD>)，持续4秒。<br><br>冷却时间： 60秒",
                            "shortDescription": "当你在承受即将使生命值降至危险线的魔法伤害时，提供一个魔法伤害护盾。",
                            "runeId": 8224
                        },
                        {
                            "name": "法力流系带",
                            "longDescription": "每过75秒，你使用的下一个技能将返还它的法力或能量消耗，并回复你8%已损失的法力或能量。",
                            "shortDescription": "周期性地使你使用的下一个技能返还它的法力或能量消耗，并为你回复一些已损失的法力或能量。",
                            "runeId": 8226
                        },
                        {
                            "name": "终极技能帽",
                            "longDescription": "你的终极技能的冷却时间缩短5%。每当你施放你的终极技能时，它的冷却时间就会进一步地缩短2%。最多可叠加5次。",
                            "shortDescription": "你的终极技能的冷却时间得到了缩减。并且你每次使用终极技能时，它的冷却时间就会得到进一步缩减。",
                            "runeId": 8243
                        }
                    ],
                    "name": "宝物"
                },
                {
                    "runes": [
                        {
                            "name": "超然",
                            "longDescription": "在你到达10级时获得10%冷却缩减。<br><br>每百分比溢出的冷却缩减会转化为一个<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应的</font></lol-uikit-tooltipped-keyword>加成，1.2攻击力或2法术强度。",
                            "shortDescription": "在你到达10级时获得10%<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_CDR'>冷却缩减</lol-uikit-tooltipped-keyword>。溢出的冷却缩减会转化为法术强度或攻击力，<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'>自动适应</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8210
                        },
                        {
                            "name": "迅捷",
                            "longDescription": "获得3%移动速度<br>你的额外移动速度会转化为攻击力或法术强度，以4.8%攻击力或8%法术强度的转化率来<font color='#48C4B7'>自动适应</font>",
                            "shortDescription": "获得3%额外<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_MS'>移动速度</lol-uikit-tooltipped-keyword>。获得额外的法术强度或攻击力，基于你的额外移速<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'>自动适应</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8234
                        },
                        {
                            "name": "绝对专注",
                            "longDescription": "在高于70%生命值时，获得一个<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应的</font></lol-uikit-tooltipped-keyword>加成，24攻击力或40法术强度(基于等级)",
                            "shortDescription": "在高于70%生命值时，获得额外的<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_AdaptiveDmg\">自适应伤害</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8233
                        }
                    ],
                    "name": "卓越"
                },
                {
                    "runes": [
                        {
                            "name": "焦灼",
                            "longDescription": "你的下一个技能在命中时会使敌方英雄燃烧，在1秒后造成20 - 60额外魔法伤害（基于等级）。<br><br>冷却时间：20秒",
                            "shortDescription": "每过20秒，你的第一个技能在命中时会使敌方英雄燃烧。",
                            "runeId": 8237
                        },
                        {
                            "name": "水上行走",
                            "longDescription": "在河道时，获得25移动速度并获得一个<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应的</font></lol-uikit-tooltipped-keyword>加成，18攻击力或30法术强度(基于等级)。<br><br><hr></hr><br><i>愿你如奔腾的河流一样迅捷，如受惊的峡谷迅捷蟹一样机敏</i><br>",
                            "shortDescription": "在河道时提供<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_MS\">移动速度</lol-uikit-tooltipped-keyword>和法术强度或攻击力，<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_Adaptive\">自动适应</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8232
                        },
                        {
                            "name": "风暴聚集",
                            "longDescription": "每10分钟获得法术强度或攻击力，<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应</font></lol-uikit-tooltipped-keyword>。<br><br><i>10分钟</i>: + 8法术强度 或 5攻击力<br><i>20分钟</i>: + 24法术强度 或 14攻击力<br><i>30分钟</i>: + 48法术强度 或 29攻击力<br><i>40分钟</i>: + 80法术强度 或 48攻击力<br><i>50分钟</i>: + 120法术强度 或 72攻击力<br><i>60分钟</i>: + 168法术强度 或 101攻击力<br>以此类推……",
                            "shortDescription": "随着游戏的进程而获得数额不断提升的攻击力或法术强度，<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'>自动适应</lol-uikit-tooltipped-keyword>。",
                            "runeId": 8236
                        }
                    ],
                    "name": "威能"
                }
            ]
        },
        {
            "name": "启迪",
            "slogan": "智胜区区凡人",
            "description": "创造性的工具并弯曲规则",
            "bonuses": [
                {
                    "name": "启迪+主宰",
                    "value": "+13攻击力或+22法术强度，<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应</font></lol-uikit-tooltipped-keyword>"
                },
                {
                    "name": "启迪+巫术",
                    "value": "+13攻击力或+22法术强度，<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Adaptive'><font color='#48C4B7'>自动适应</font></lol-uikit-tooltipped-keyword>"
                },
                {
                    "name": "启迪+坚决",
                    "value": "+145生命值"
                },
                {
                    "name": "启迪+精密",
                    "value": "+20%攻击速度"
                }
            ],
            "slots": [
                {
                    "runes": [
                        {
                            "name": "启封的秘籍",
                            "longDescription": "在2分钟时获得一块【召唤师碎片】，并且之后的每6分钟获得另一块。(最多2块碎片).<br><br>在商店附近时，你可以兑换1块召唤师碎片来将一个召唤师技能换成另一个不同的召唤师技能。<br><br>此外，你的召唤师技能冷却时间会减少15%。<br><br><rules><i>惩戒：</i>购买【惩戒】并不会获得打野装的购买权<br>你无法拥有两个相同的召唤师技能</rules>",
                            "shortDescription": "可在单局游戏期间用【召唤师碎片】在商店中更改你的召唤师技能。你的召唤师技能会拥有更短冷却时间。<br>",
                            "runeId": 8326
                        },
                        {
                            "name": "冰川增幅",
                            "longDescription": "对一名英雄进行普攻时还会使该英雄减速2秒。减速效果会在持续期间不断增强。<li><i>远程</i>：远程攻击最多可减速30% - 40% </li> <li><i>近战</i>：近战攻击最多可减速45% - 55%</li> <br>用主动型装备减速一名英雄时，还会发射一束冻结射线来穿透该英雄，冻住附近的地面5秒，使其中的所有单位减速60%。<br><br>冷却时间：每个单位7-4秒",
                            "shortDescription": "你对敌方英雄发起的第一次攻击会使该英雄减速(一段时间内无法重复作用于相同目标)。用主动型装备使敌方英雄减速，还会对着他们发射冻结射线，并创造出一个缓慢消失的减速场。",
                            "runeId": 8351
                        },
                        {
                            "name": "行窃预兆",
                            "longDescription": "在使用一次技能后，你的下次攻击如果是对英雄发起的，将提供额外金币。你还有一定几率获得一个消耗品。",
                            "shortDescription": "在使用一次技能后，你的下次攻击如果是对英雄发起的，将提供额外金币。你还有一定几率获得一个消耗品。",
                            "runeId": 8359
                        }
                    ],
                    "name": "基石"
                },
                {
                    "runes": [
                        {
                            "name": "海克斯科技闪现罗网",
                            "longDescription": "当【闪现】尚未冷却完毕时，它会被替换为<i>海克斯闪现</i>。<br><br><i>海克斯闪现</i>：引导2秒后闪烁到一个新位置。<br><br>冷却时间：20秒。在你进入与英雄战斗的状态时，会有一个10秒的冷却时间。<br>",
                            "shortDescription": "当【闪现】尚未冷却完毕时，它会被替换为<i>海克斯闪现</i>。<br><br><i>海克斯闪现</i>：引导，然后闪烁到一个新位置。",
                            "runeId": 8306
                        },
                        {
                            "name": "神奇之鞋",
                            "longDescription": "你在10分钟时获得免费的有点神奇之靴，但你在之前不能购买鞋子。你每参与一次击杀，就会使获得鞋子的时间点提前30秒。<br><br>有点神奇之靴可为你提供额外的+10移动速度。",
                            "shortDescription": "你在10分钟时获得免费的鞋子，但你在之前不能购买鞋子。每次<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Takedown'>协助击杀</lol-uikit-tooltipped-keyword>都会让你的鞋子的到来时间提前30秒",
                            "runeId": 8304
                        },
                        {
                            "name": "完美时机",
                            "longDescription": "开始游戏时带着一个一次性使用的始动的秒表，可用来进入凝滞状态。秒表在最初的10分钟里无法被使用。<br><br>使中娅沙漏，守护天使,，和石像鬼石板甲的冷却时间减少15%。",
                            "shortDescription": "获得一个免费的秒表。秒表有一个一次性使用的<lol-uikit-tooltipped-keyword key='LinkTooltip_Description_Stasis'>凝滞</lol-uikit-tooltipped-keyword>主动效果。",
                            "runeId": 8313
                        }
                    ],
                    "name": "巧具"
                },
                {
                    "runes": [
                        {
                            "name": "未来市场",
                            "longDescription": "你可以欠债来购买装备。你可以欠的数额会随时间增加。<br><br>借款费用：50金币<br>欠款限额：150 + 5/分钟<br>(欠款需要在2分钟后才可使用)。",
                            "shortDescription": "你可以欠债来购买装备。",
                            "runeId": 8321
                        },
                        {
                            "name": "小兵去质器",
                            "longDescription": "开始游戏时会带着6个能够立刻击杀并吸收线上小兵的小兵去质器。小兵去质器在游戏最初的155秒内处于冷却状态。<br><br>吸收一个小兵会使你对该类型小兵的伤害永久提升+4%，并且每吸收另一种类型的小兵，这个伤害加成就会提升+1%。<br>",
                            "shortDescription": "开始游戏时会带着6个能够秒杀线上小兵的小兵去质器。用该物品击杀小兵会永久提升你对该类型小兵的额外伤害。",
                            "runeId": 8316
                        },
                        {
                            "name": "饼干配送",
                            "longDescription": "饼干配送：每过3分钟获得一个永续意志夹心饼干，直到12分钟为止。<br><br>饼干可回复你15%的已损失生命值和法力值。使用一个饼干可永久给予你40最大法力值。<br><br><i>没有法力的</i>英雄会回复20%已损失生命值。",
                            "shortDescription": "每过3分钟获得一个免费的【饼干】，直到12分钟为止。饼干可回复生命值和法力值。享用一个饼干可永久提升你的最大法力值。",
                            "runeId": 8345
                        }
                    ],
                    "name": "未来"
                },
                {
                    "runes": [
                        {
                            "name": "星界洞悉",
                            "longDescription": "+5%冷却缩减<br>+5%最大冷却缩减<br>+5%召唤师技能冷却缩减<br>+5%装备冷却缩减",
                            "shortDescription": "+5%<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_CDR\">冷却缩减</lol-uikit-tooltipped-keyword><br>+5%最大冷却缩减<br>+5%召唤师技能冷却缩减<br>+5%装备冷却缩减",
                            "runeId": 8347
                        },
                        {
                            "name": "行近速率",
                            "longDescription": "朝着附近移动受损的友方英雄或敌方英雄移动时，将获得10%移动速度加成。<br><br>距离：1000",
                            "shortDescription": "朝着<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_ImpairMov\">移动受损</lol-uikit-tooltipped-keyword>的友方英雄被你施加了移动受损效果的敌方英雄移动时，获得<lol-uikit-tooltipped-keyword key=\"LinkTooltip_Description_MS\">移动速度</lol-uikit-tooltipped-keyword>加成。",
                            "runeId": 8410
                        },
                        {
                            "name": "时间扭曲补药",
                            "longDescription": "你的药水、饼干和合剂的持续时间延长20%，并且你在以上物品的效果下时会获得5%移动速度。",
                            "shortDescription": "你的药水、饼干和合剂的持续时间延长20%，并且你在以上物品的效果下时会获得5%移动速度。",
                            "runeId": 8352
                        }
                    ],
                    "name": "超越"
                }
            ]
        }
    ]
})