import React from 'react'

import FloatingActionButton from 'material-ui/FloatingActionButton';
import ActionHome from 'material-ui/svg-icons/action/assessment';
import ActionDelete from 'material-ui/svg-icons/action/delete';
import ActionAssessment from 'material-ui/svg-icons/action/assessment';
import ContentAdd from 'material-ui/svg-icons/content/add'
import EditorModeEdit from 'material-ui/svg-icons/editor/mode-edit';

import { buttonStyle } from '../../constants/styles'

const SectionButton = React.createClass({

    propTypes: {
        title: React.PropTypes.string,
        onTouchTap: React.PropTypes.func
    },

    render: function() {
        const { title, onTouchTap, children } = this.props;

        const renderIcon = (title) => {
            switch (title) {
                case 'Add':
                    return (
                        <ContentAdd />
                    )
                case 'Edit':
                    return (
                        <EditorModeEdit />
                    )
                case 'Delete':
                    return (
                        <ActionDelete />
                    )
                case 'View':
                    return (
                        <ActionHome />
                    )
                default:
                    return null;
            }
        }

        return (
            <FloatingActionButton
                onTouchTap={ onTouchTap }
                title={ title }
                style={ buttonStyle }
                secondary={ true }
            >
                { renderIcon(title) }
            </FloatingActionButton>
        );
    }
})

module.exports.SectionButton = SectionButton
