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
    window.console.log(str);
    if (typeof str !== 'string') {
        return 0;
    }

    const doc = new DOMParser().parseFromString(
        str.replace(/<\/?(p|div|br|li|h[1-6])\b[^>]*>/gi, ' '),
        'text/html'
    );
    return doc.body.textContent.split(/\s+/).filter(Boolean).length;
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
    let textbox = wordcountwrapper.previousElementSibling;
    let text = textbox.matches('textarea') ? textbox?.value : textbox?.innerHTML;
    wordcount.innerText = countWords(text);

    textbox?.addEventListener('input', e => {
        wordcount.innerText = countWords(e.target.value);
    });
};