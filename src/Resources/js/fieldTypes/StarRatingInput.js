// @flow
import React from 'react';
import { observer } from 'mobx-react';
import type { FieldTypeProps } from 'sulu-admin-bundle/types';
import StarRating from './StarRating';

@observer
class StarRatingInput extends React.Component<FieldTypeProps<number>> {
    handleChange = (value: number) => {
        const { onChange, onFinish } = this.props;
        onChange(value);
        if (onFinish) {
            onFinish();
        }
    };

    render() {
        const { value, schemaOptions } = this.props;

        const max = 5;

        return (
            <StarRating
                value={Number(value) || 0}
                onChange={this.handleChange}
                max={max}
            />
        );
    }
}

export default StarRatingInput;