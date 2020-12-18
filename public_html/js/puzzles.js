let res = $('#response'),
    board = $('#chessground'),
    mainHTML = $('#main');
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
        enabled: true
    },
    animation: {
        duration: 300
    }
},
cg = Chessground(board,config);
cg.set({
    movable: {
        events: {
            after: checkMove(chess,cg)
        }
    }
});

let turn = chess.turn() === 'w' ? 'White':'Black',
    gaveTrophy = false,
    fullMove = 0,
    explained = false,
    isPuzzlePreview = false; // Is the puzzle explanation text showing

if (info.puzzlePreview) {
    isPuzzlePreview = true;
}

function showResponse(s,c,r,d,e) {
    // s = solved, c = complete, r[0] = puzzle new rating & r[1] = user's new rating, d = user rating diff between new and old rating, e = puzzle explanation, aka poorly written variable names
    d = Math.round(d);
    const unrated = isNaN(d);
    if (s && !c) {
        res.innerHTML = '<i class="fa fa-check"></i> Good move';
        res.classList = 'correct';
    } else if (c) {
        let viewExplainEl, explainEl;
        $('#copyings').style.display = 'block';
        $('#next').style.display = 'block';
        $('#credits').style.display = 'block';
        setTimeout(()=>{
            $('#copyings').classList.remove('hidden');
            $('#next').classList.remove('hidden');
            $('#credits').classList.remove('hidden');
        },250);
        if (e) {
            viewExplainEl = document.createElement('span');
            explainEl = document.createElement('div');
            viewExplainEl.innerHTML = '<i class="fa fa-angle-down"></i> View explanation';
            viewExplainEl.classList = 'view-explanation end-stuff';
            explainEl.classList = 'explanation';
            explainEl.innerHTML = e;
            viewExplainEl.addEventListener('click',()=>explain(explainEl,viewExplainEl));
        }
        if (s) {
            res.innerHTML = '<i class="fa fa-check"></i> Puzzle solved';
            res.classList = 'correct';
        } else {
            res.innerHTML = '<i class="fa fa-close"></i> Puzzle failed';
            res.classList = 'incorrect';
            const retryBtn = document.createElement('span');
            retryBtn.innerHTML = '<i class="fa fa-undo"></i> Retry';
            retryBtn.classList = 'retry end-stuff';
            retryBtn.addEventListener('click',()=>window.location.reload());
            res.appendChild(retryBtn);
            $('#next').children[0].classList.remove('green'); // Green doesn't really work with red in this case.
        }
        const lichess = document.createElement('a');
        lichess.href = `https://lichess.org/analysis/${fen}`;
        lichess.innerHTML = '<i class="fa fa-external-link"></i> Analyze on lichess</i>';
        lichess.target = '_blank'; 
        lichess.classList = 'end-stuff';
        res.appendChild(lichess);
        if (e) {
            res.appendChild(viewExplainEl);
            res.appendChild(explainEl);
        }
        let extraStyle = '';
        if (!loggedin || author === infoUsername) {
            extraStyle = ' disabled';
            if (!loggedin) {
                extraStyle += ' data-hint="Log in to like this puzzle"';
            } else {
                extraStyle += ' data-hint="You can\'t give your puzzle a trophy"';
            }
        } else {
            extraStyle += ' data-hint="Keyboard shortcut: t"';
        }
        let el;
        if (!isPuzzlePreview) {
            el = document.createElement('div');
            el.classList = 'give-a-trophy';
            el.innerHTML = `<button type="submit" id="trophy" class="trophy"${extraStyle}><i class="fa fa-trophy"></i></button> <span id="tCount">${trophies}</span>`;
        }
        const cont = $('#credits');
        const el2 = document.createElement('div');
        el2.innerHTML = `<p>Puzzle created by <a href="/member/${author.toLowerCase()}">${author}</a></p>
                        <p>Puzzle rating: ${Math.round(r[0])}</p>`;
        if (!unrated) {
            el2.innerHTML += `<p>Your rating: ${Math.round(r[1])} (${d > 0 ? '+' + d : '–' + (d * -1)})</p>`;
        } else {
            el2.innerHTML += `<p>Unrated</p>`;
        }
        el2.classList = 'inner';
        cont.appendChild(el2); //Don't ask why they're in reverse
        if (!isPuzzlePreview) {
            cont.appendChild(el);
        }
        if (loggedin && author !== infoUsername && !isPuzzlePreview) {
            $('#trophy').addEventListener('click',updateTrophies);
            document.body.addEventListener('keyup',e=>{
                if (e.key.toLowerCase() === 't') {
                    updateTrophies();
                }
            });
        }
    }
}
function explain(el,vEl) {
    if (explained) {
        el.classList.remove('open');
        vEl.children[0].classList.remove('fa-angle-up');
        vEl.children[0].classList.add('fa-angle-down');
    } else {
        el.classList.add('open');
        vEl.children[0].classList.add('fa-angle-up');
        vEl.children[0].classList.remove('fa-angle-down');
    }
    explained = !explained;
}
function checkMove(c,cg) {
    return async (o,d) => {
        fullMove++;
        const mObj = { from: o, to: d, promotion: 'q' };
        let m = chess.move(mObj);
        if (m.flags.includes('p')) {
            chess.undo();
            const promote = await openPromoteOptions(board,m.to,cg,getColor(chess.turn()));
            if (promote) {
                m = chess.move({ from: o, to: d, promotion: promote });
                getMoves(m);
            }
        } else {
            getMoves(m);
        }
    }
}
function getMoves(m) {
    res.classList.add('loading');
    let loaderClasses = 'loader ' + (fullMove > 1 ? 'white' : ''); // Blue on green doesn't contrast well enough.
    res.innerHTML = `<div class="${loaderClasses}"></div>`;
    const urlAddPreview = isPuzzlePreview ? '&preview=true' : '',
          urlExtension = isPuzzlePreview ? '' : '../';
          xhr = new XMLHttpRequest(),
          url = `${urlExtension}getmoves?move=${m.san.replace('+','%2B').replace('#','%23').replace('=','%3D')}&movenum=${fullMove}&puzzle=${pID}${urlAddPreview}`;
    xhr.responseType = 'json';
    xhr.onreadystatechange = function() {
        if (xhr.readyState === xhr.DONE) {
            const resp = xhr.response;
            if (resp.correct && !resp.ended) {
                showResponse(true,false);
                const m2 = chess.move(resp.next);
                cg.move(m2.from,m2.to);
                const piece = {
                    'Q':'queen',
                    'R':'rook',
                    'B':'bishop',
                    'N':'knight'
                }
                let getPiece = resp.next.replace(/[^a-z]/gi,'').split('');
                if (m2.flags.includes('p')) promote(cg,m2.to,piece[getPiece[getPiece.length - 1]]);
                cg.set({
                    turnColor: getColor(chess.turn()),
                    movable: {
                        color: getColor(chess.turn()),
                        dests: toDests(chess)
                    }
                });
                cg.playPremove();
            } else if (resp.ended) {
                if (resp.correct) {
                    showResponse(true,true,[resp.ratings.puzzle,resp.ratings.user],resp.rating_diff,resp.explanation);
                    cg.stop();
                } else {
                    showResponse(false,true,[resp.ratings.puzzle,resp.ratings.user],resp.rating_diff,resp.explanation);
                    setTimeout(()=>{
                        chess.undo();
                        console.log(chess.ascii());
                        cg.set({
                           fen: chess.fen(),
                        });
                        let rightMove = chess.move(resp.right_move);
                        console.log(rightMove);
                        const shape = [{
                            orig: rightMove.from,
                            dest: rightMove.to,
                            brush: 'green'
                        }];
                        cg.setShapes(shape);
                        cg.stop();
                    }, 500);
                }
            }
        }
    }
    xhr.open('GET',url);
    xhr.send();
}
function updateTrophies() {
    if (!gaveTrophy && !isPuzzlePreview) {
        const t = $('#tCount');
        t.innerHTML = '...';
        const xhr = new XMLHttpRequest(),
              url = '/puzzles/star',
              data = `trophy=1&puzzle=${pID}`;
        xhr.responseType = 'json';
        xhr.onreadystatechange = function() {
            if (xhr.readyState === xhr.DONE) {
                const res = xhr.response;
                t.innerHTML = res.count;
                $('#trophy').style.pointerEvents = 'none';
            }
        }
        xhr.open('POST',url);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
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

function updateBoardSize() {
    let width = mainHTML.offsetWidth - 40;
    if (width % 8 !== 0) {
        width -= width % 8; // Multiples of 8 are better for the board display
    }
    board.style.height = board.style.width = width + 'px';
    cg.redrawAll();
}

$('#puzzleURL').value = window.location.href;

res.innerHTML = `<i class="fa fa-info-circle"></i> ${turn} to move`;

const inputs = $('input',true);
inputs.forEach(i=>{
    i.addEventListener('click',()=>{
        i.select();
    });
});

$('.copy-on-click',true).forEach((el,i)=>{
    el.addEventListener('click',()=>{
        el.select();
        document.execCommand('copy');
        $(`#copy-${i+1}`).classList.add('copied');
        setTimeout(()=>{
            $(`#copy-${i+1}`).classList.remove('copied');
        },2000);
    });
});

window.addEventListener('resize',updateBoardSize);

updateBoardSize();
