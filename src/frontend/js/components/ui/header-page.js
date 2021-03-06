import React from 'react'
import classNames from 'classnames';

const HeaderPage = React.createClass({


    propTypes: {
        title: React.PropTypes.string,
        subtitle: React.PropTypes.string,
        className: React.PropTypes.string
    },

    render: function() {
        const { title, subtitle, className, children } = this.props;
        const subtitleNode = (subtitle) =>  {
            if (!subtitle)
                return null;
            return (
                <div className="header-page-subtitle">{ subtitle }</div>
            )
        };
        return (
            <div className={ classNames([className, 'header-page clearfix']) }>
                <div className="pull-left">
                    <div className="header-page-title">{ title }</div>
                    { subtitleNode(subtitle)}
                </div>
                <div className="pull-right">{ children }</div>
            </div>
        );
    }
})

module.exports.HeaderPage = HeaderPage
