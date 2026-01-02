// @flow
import React from 'react';
import {observer} from 'mobx-react';
import {SingleSelect} from 'sulu-admin-bundle/components';
import {toJS} from 'mobx';
import type {FieldTypeProps} from 'sulu-admin-bundle/types';
import starRatingStyles from './StarRating.scss';

type SchemaOptions = {
    values?: {
        value: Array<{name: string, title?: string}>,
    },
    max_value?: {
        value: number,
    },
    show_text?: {
        value: boolean,
    },
};

const renderStars = (rating: number, maxValue: number, showText: boolean) => {
    const displayStars = 5;
    const fillPercent = (rating / maxValue) * 100;

    const backgroundStars = [];
    const foregroundStars = [];

    for (let i = 1; i <= displayStars; i++) {
        backgroundStars.push(<span key={i} className={starRatingStyles.starIcon}>★</span>);
        foregroundStars.push(<span key={i} className={starRatingStyles.starIcon}>★</span>);
    }

    return (
        <span className={starRatingStyles.dropdownOption}>
            <span className={starRatingStyles.dropdownStars}>
                <span className={starRatingStyles.starsBackground}>{backgroundStars}</span>
                <span className={starRatingStyles.starsForeground} style={{width: `${fillPercent}%`}}>
                    {foregroundStars}
                </span>
            </span>
            {showText && (
                <span className={starRatingStyles.dropdownText}>({rating}/{maxValue})</span>
            )}
        </span>
    );
};

@observer
class StarRatingSelect extends React.Component<FieldTypeProps<string, SchemaOptions>> {
    handleChange = (value: string | number) => {
        const {onChange, onFinish} = this.props;
        onChange(value);
        onFinish();
    };

    render() {
        const {dataPath, error, value, schemaOptions} = this.props;

        const rawValues: Array<{name: string, title?: string}> = toJS(schemaOptions?.values?.value || []);
        const maxValue: number = schemaOptions?.max_value?.value || 10;
        const showText: boolean = schemaOptions?.show_text?.value !== false;

        return (
            <SingleSelect
                id={dataPath}
                value={value}
                onChange={this.handleChange}
                valid={!error}
            >
                {rawValues.map((item) => {
                    const rating = parseInt(item.name, 10) || 0;
                    return (
                        <SingleSelect.Option key={item.name} value={item.name}>
                            {renderStars(rating, maxValue, showText)}
                        </SingleSelect.Option>
                    );
                })}
            </SingleSelect>
        );
    }
}

export default StarRatingSelect;