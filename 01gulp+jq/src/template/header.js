let HEADER = `
<div class="path-selector">
    <div class="path-selector_row">
        {{tabs}}
        <a id="team-collapse-selector" href="javascript:;">
            战队介绍
            <i class="path-caret"></i>
        </a>
        <div id="team-collapse" class="team-collapse">
            <div class="team-collapse-header">
                <span class="active" data-game="lol">英雄联盟</span>
                <span class="white-tag">|</span>
                <span data-game="kog">王者荣耀</span>
            </div>
            <div id="team-collapse-list" class="team-collapse-list"></div>
        </div>
    </div>
</div>
`;

const tabs = [
    {
        href: './schedule.html',
        name: '赛程',
        key: 'schedule'
    }, {
        href: './rank-points.html',
        name: '排行榜',
        key: ['rank-hero', 'rank-player', 'rank-points', 'rank-team']
    }
];

function _isInTabs(page, keys) {
    if (typeof keys === 'string') {
        return keys === page;
    } else if (Array.isArray(keys)) {
        return keys.indexOf(page) !== -1;
    }
}

function buildHeader(page) {
    console.log(page);
    let tabsHTML = tabs.map(item => {
        let activeClass = _isInTabs(page, item.key)
            ? 'active'
            : '';

        return `<a href="${item.href}" class="${activeClass}">${item.name}</a>`;
    });

    return HEADER.replace('{{tabs}}', tabsHTML.join(''));
}

module.exports = buildHeader;