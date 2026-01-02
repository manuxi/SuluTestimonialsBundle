// @flow
import React from 'react';
import {observer} from 'mobx-react';
import {SingleSelect} from 'sulu-admin-bundle/components';
import {toJS} from 'mobx';
import type {FieldTypeProps} from 'sulu-admin-bundle/types';
import starRatingStyles from './StarRating.scss';

const renderStars = (label: string) => {
    const match = label.match(/^([★⯪☆]+)\s*(.*)$/);

    if (!match) {
        return label;
    }

    const starsString = match[1];
    const text = match[2];

    const stars = starsString.split('').map((char, index) => {
        let className = starRatingStyles.dropdownStar;
        if (char === '☆') {
            className += ' ' + starRatingStyles.empty;
        }
        return (
            <span key={index} className={className}>
                {char}
            </span>
        );
    });

    return (
        <div className={starRatingStyles.dropdownOption}>
            <span className={starRatingStyles.dropdownStars}>{stars}</span>
            <span className={starRatingStyles.dropdownText}>{text}</span>
        </div>
    );
};

@observer
class StarRatingSelect extends React.Component<FieldTypeProps<string>> {
    handleChange = (value: string | number) => {
        const {onChange, onFinish} = this.props;
        onChange(value);
        onFinish();
    };

    render() {
        const {dataPath, error, value, schemaOptions} = this.props;

        const rawValues: Array<any> = toJS(schemaOptions?.values?.value || []);

        const selectValues = rawValues.map((item) => ({
            value: item.name,
            label: item.title || item.name,
        }));

        return (
            <SingleSelect
                id={dataPath}
                value={value}
                onChange={this.handleChange}
                valid={!error}
            >
                {selectValues.map((option) => (
                    <SingleSelect.Option key={option.value} value={option.value}>
                        {renderStars(option.label)}
                    </SingleSelect.Option>
                ))}
            </SingleSelect>
        );
    }
}

export default StarRatingSelect;