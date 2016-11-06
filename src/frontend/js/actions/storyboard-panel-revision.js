import request from 'superagent';
import {
    GET_STORYBOARD_PANEL_REVISION_REQUEST,
    GET_STORYBOARD_PANEL_REVISION_ERROR,
    GET_STORYBOARD_PANEL_REVISION_SUCCESS,
    POST_STORYBOARD_PANEL_REVISION_REQUEST,
    POST_STORYBOARD_PANEL_REVISION_ERROR,
    POST_STORYBOARD_PANEL_REVISION_SUCCESS,
    PUT_STORYBOARD_PANEL_REVISION_REQUEST,
    PUT_STORYBOARD_PANEL_REVISION_ERROR,
    PUT_STORYBOARD_PANEL_REVISION_SUCCESS,
} from '../constants/actions';
import {
    FORM_MODE_ADD,
    FORM_MODE_EDIT
} from '../constants/form';

const getStoryboardPanelRevisionInit = () => {
    return {
        type: GET_STORYBOARD_PANEL_REVISION_REQUEST
    }
}

const getStoryboardPanelRevisionSuccess = (project, storyboard, panel, revision, form_mode) => {
    return {
        type: GET_STORYBOARD_PANEL_REVISION_SUCCESS,
        project,
        storyboard,
        panel,
        revision,
        form_mode
    }
}

const getStoryboardPanelRevisionError = () => {
    return {
        type: GET_STORYBOARD_PANEL_REVISION_ERROR
    }
}

export const getStoryboardPanelRevision = (projectId, storyboardId, panelId, revisionId) =>
    dispatch => {
        dispatch(getStoryboardPanelRevisionInit());
        request.get(`/api/project/${projectId}`)
            .then((res) => {
                const project = res.body;
                const storyboard = _.findWhere(project.storyboards, {
                    'id': parseInt(storyboardId)
                });
                const panel = _.findWhere(storyboard.panels, {
                    'id': parseInt(panelId)
                });
                const revision = _.findWhere(panel.revisions, {
                    'id': parseInt(revisionId)
                });
                const form_mode = revision ? FORM_MODE_EDIT : FORM_MODE_ADD;

                dispatch(getStoryboardPanelRevisionSuccess(
                    project,
                    storyboard,
                    panel,
                    revision,
                    form_mode));
            })
            .catch((error) => {
                console.log(error);
                dispatch(getStoryboardPanelRevisionError())
            });
    };


const postStoryboardPanelRevisionInit = () => {
    return {
        type: POST_STORYBOARD_PANEL_REVISION_REQUEST
    }
}

const postStoryboardPanelRevisionSuccess = (project, storyboard, panel, revision) => {
    return {
        type: POST_STORYBOARD_PANEL_REVISION_SUCCESS,
        project,
        storyboard,
        panel,
        revision,
        form_mode: FORM_MODE_EDIT
    }
}

const postStoryboardPanelRevisionError = (errors) => {
    return {
        type: POST_STORYBOARD_PANEL_REVISION_ERROR,
        errors
    }
}

export const postStoryboardPanelRevision = ( project, storyboard, panel, fields) =>
    dispatch => {
        dispatch(postStoryboardPanelRevisionInit());
        request.post('/api/project_storyboard_panel_revision')
            .send({ ...fields, panel_id: panel.id })
            .end((err, res) => {
                if(res.ok) {
                    const revision = res.body;
                    dispatch(postStoryboardPanelRevisionSuccess(project, storyboard, panel, revision));
                }

                if(!res.ok)
                    dispatch(postStoryboardPanelRevisionError(res.body))
            });
    };

const putStoryboardPanelRevisionInit = () => {
    return {
        type: PUT_STORYBOARD_PANEL_REVISION_REQUEST
    }
}

const putStoryboardPanelRevisionSuccess = (project, storyboard, panel, revision) => {
    return {
        type: PUT_STORYBOARD_PANEL_REVISION_SUCCESS,
        project,
        storyboard,
        panel,
        revision,
        form_mode: FORM_MODE_EDIT
    }
}

const putStoryboardPanelRevisionError = () => {
    return {
        type: PUT_STORYBOARD_PANEL_REVISION_ERROR
    }
}

export const putStoryboardPanelRevision = (project, storyboard, panel, revision, fields) =>
    dispatch => {
        dispatch(putStoryboardPanelRevisionInit());
        request.put('/api/project_storyboard_panel_revision/' + revision.id)
            .send({ ...fields, panel_id: panel.id })
            .then((res) => {
                const r = res.body;
                dispatch(putStoryboardPanelRevisionSuccess(project, storyboard, panel, r));
            })
            .catch((error) => {
                console.log(error);
                dispatch(putStoryboardPanelRevisionError())
            });
    };
