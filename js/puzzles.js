let res = document.getElementById('response');
const chess = new Chess(fen), config = {
    fen: fen,
    coordinates: false,
    turnColor: getColor(chess.turn()),
    orientation: getColor(chess.turn()),
    movable: {
        free: false,
        color: getColor(chess.turn()),
        dests: toDests(chess)
    },
    premovable: {
        enabled: false
    },
    animation: {
        duration: 300
    }
}, cg = Chessground(document.getElementById('chessground'),config);
cg.set({
    movable: {
        events: {
            after: checkMove(chess,cg)
        }
    }
});

let turn = chess.turn() === 'w' ? 'White':'Black',
    gaveTrophy = false,
    fullMove = 0;

function showResponse(s,d) {
    if (s && !d) {
        res.innerHTML = '<i class="fa fa-check"></i> Good move';
        res.classList = 'correct';
    } else if (s && d) {
        res.innerHTML = '<i class="fa fa-check"></i> Puzzle solved';
        res.classList = 'correct';
        let extraStyle = '';
        if (!loggedin || author === infoUsername) {
            extraStyle = ' style="pointer-events:none"';
        }
        const el = document.createElement('div');
        el.classList = 'give-a-trophy';
        el.innerHTML = `<button type="submit" id="trophy" class="trophy"${extraStyle}><i class="fa fa-trophy"></i></button> <span id="tCount">${trophies}</span>`;
        document.getElementById('res-container').appendChild(el);
        document.getElementById('trophy').addEventListener('click',updateTrophies);
    } else {
        res.innerHTML = '<i class="fa fa-close"></i> Wrong move';
        res.classList = 'incorrect';
    }
}
function toDests(c) {
    const dests = {};
    c.SQUARES.forEach(s => {
        const ms = c.moves({square: s, verbose: true});
        if (ms.length) dests[s] = ms.map(m => m.to);
    });
    return dests;
}
function checkMove(c,cg) {
    return (o,d) => {
        res.classList.add('loading');
        res.innerHTML = '<div class="loader"></div>';
        fullMove++;
        const mObj = { from: o, to: d,promotion: 'q' };
        const m = chess.move(mObj);
        const xhr = new XMLHttpRequest(),
              url = `../getmoves?move=${m.san.replace('+','%2B').replace('#','%23')}&movenum=${fullMove}&puzzle=${pID}`;
        xhr.responseType = 'json';
        xhr.onreadystatechange = function() {
            if (xhr.readyState === xhr.DONE) {
                const resp = xhr.response;
                if (resp.correct && !resp.ended) {
                    showResponse(true,false);
                    const m2 = chess.move(resp.next);
                    cg.move(m2.from,m2.to);
                    cg.set({
                        turnColor: getColor(chess.turn()),
                        movable: {
                            color: getColor(chess.turn()),
                            dests: toDests(chess)
                        }
                    });
                } else if (resp.correct) {
                    showResponse(true,true);
                } else {
                    showResponse(false,false);
                    setTimeout(()=>{
                        chess.undo();
                        cg.set({
                            fen: chess.fen(),
                            turnColor: getColor(chess.turn()),
                            movable: {
                                color: getColor(chess.turn()),
                                dests: toDests(chess)
                            }
                        });
                    },500);
                    fullMove--;
                }
            }
        }
        xhr.open('GET',url);
        xhr.send();
    }
}
function getColor(c) {
    return c === 'w' ? 'white':'black';
}
function updateTrophies(e) {
    if (!gaveTrophy) {
        const t = document.getElementById('tCount');
        t.innerHTML = '...';
        const xhr = new XMLHttpRequest(),
              url = '/puzzles/star',
              data = `trophy=1&puzzle=${pID}`;
        xhr.responseType = 'json';
        xhr.onreadystatechange = function() {
            if (xhr.readyState === xhr.DONE) {
                const res = xhr.response;
                t.innerHTML = res.count;
                document.getElementById('trophy').style.pointerEvents = 'none';
            }
        }
        xhr.open('POST',url);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
        xhr.send(data);
        gaveTrophy = true;
    }
}
cg.set({
    movable: {
        events: {
            after: checkMove(chess,cg)
        }
    }
});

document.getElementById('puzzleURL').innerHTML = window.location.href;
document.getElementById('puzzleFEN').innerHTML = fen;

res.innerHTML = `<i class="fa fa-info-circle"></i> ${turn} to move`;
