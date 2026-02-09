// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A JavaScript module for the word count in essay question type using plain text format
 *
 * @copyright  2026 Amanda Doughty <m.doughty@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const SELECTORS = {
    WORD_COUNT: '[data-region="wordcount"]',
};

/**
 * Count the words
 *
 * @method countWords
 * @param {String} str
 */
const countWords = (str) => {
    let res = [];
    let cleanstr = str.replace(/[\t\n\r]/gm, " ").split(" ");
    // eslint-disable-next-line array-callback-return
    cleanstr.map((s) => {
        let trimStr = s.trim();
        if (trimStr.length > 0) {
            res.push(trimStr);
        }
    });
    return res.length;
};

/**
 * Set up the word count
 *
 * @method init
 * @param {Number} Id Unique identifier
 */
export const init = (Id) => {
    let wordcountwrapper = document.getElementById(Id);
    let wordcount = wordcountwrapper.querySelector(SELECTORS.WORD_COUNT);
    let textarea = wordcountwrapper.previousSibling;
    wordcount.innerText = countWords(textarea.value);

    textarea.addEventListener('input', e => {
        wordcount.innerText = countWords(e.target.value);
    });
};