/* eslint-disable no-unused-vars */

// Functions that go with the Chess.js and Chessground libraries.

const pieceMap = {
    p: 'pawn',
    r: 'rook',
    n: 'knight',
    b: 'bishop',
    q: 'queen',
    k: 'king'
}
function getColor(c) {
    return c === 'w' ? 'white':'black';
}
function toDests(c) {
    const dests = {};
    c.SQUARES.forEach(s => {
        const ms = c.moves({square: s, verbose: true});
        if (ms.length) dests[s] = ms.map(m => m.to);
    });
    return dests;
}
function promote(g, key, role) {
    let pieces = {};
    let piece = g.state.pieces[key];
    if (piece && piece.role == 'pawn') {
        pieces[key] = {
            color: piece.color,
            role: role,
            promoted: true
        };
        g.setPieces(pieces);
    }
}
function openPromoteOptions(board,square,cg,turn) {
    // Requires popupjs
    return new Promise(r=>{
        const info = {
            html: `<button data-promote="queen" class="${turn}"></button>
            <button data-promote="rook" class="${turn}"></button>
            <button data-promote="bishop" class="${turn}"></button>
            <button data-promote="knight" class="${turn}"></button>`
        }
        const options = new Popup('custom',info);
        options.addClass('promotion-popup');
        document.body.removeChild(overlay);
        board.appendChild(overlay);
        overlay.style.position = 'absolute';
        options.open();
        options.el.querySelectorAll('button').forEach(btn=>{
            btn.addEventListener('click',()=>{
                const p = btn.getAttribute('data-promote');
                promote(cg,square,p);
                options.close();
                board.removeChild(overlay);
                document.body.appendChild(overlay);
                overlay.style.position = 'fixed';
                let promotionPiece = p.split('')[0];
                r(promotionPiece === 'k' ? 'n' : promotionPiece);
            });
        });
    });
}
function removeHeaders(pgn) {
    let splitPgn = pgn.split(']\n\n'),
        moves = splitPgn[splitPgn.length - 1];
    if (splitPgn.length === 1) {
        return pgn; // There are no headers
    }
    if (moves.startsWith('1. ...')) {
        let spliced = moves.split('');
        spliced.splice(0,6,'1...');
        return spliced.join('');
    }
    return moves;
}
