import React from 'react'

import classNames from 'classnames';

const Card = React.createClass({

    propTypes: {
        children: React.PropTypes.oneOfType([
            React.PropTypes.element,
            React.PropTypes.array
        ]).isRequired,
        className: React.PropTypes.string
    },

    render: function() {
        let className = classNames([this.props.className, 'card'])
        return (
            <div className={ className }>
                { this.props.children }
            </div>
        );
    }
})

module.exports.Card = Card