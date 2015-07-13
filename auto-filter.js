/*
	table �̃I�[�g�t�B���^
	http://neko.dosanko.us/script/auto-filter/
	2006-12-4 ��
*/

function classAutofilter()
{
	// �ݒ� �������火

	this.LABEL_FILTER_BLANK = "(��)";
	this.LABEL_FILTER_ALL   = "���ׂ�";

	this.VAL_FILTER_ALL = "__all__";

	// �ݒ� �����܂Ł�

	this.elmTbody = null;	// �����p tbody
}// classAutofilter //


// ----- public �Ȋ֐��Ƃ��Ĉ��� -----


//
// �t�B���^����
//
//	@param  strId �I�[�g�t�B���^��\������ table �� ID
//	@return       ��� (1:�t�B���^�����ς݁A0:���s�����A-1:���Ή��u���E�U)
//
classAutofilter.prototype.Create_Filter = function(strId)
{
	if( this.elmTbody != null )	return 1;

	if( !document.getElementById || !document.removeChild )	return -1;

	var elmTable     = document.getElementById(strId);
	var elmTbody     = elmTable.getElementsByTagName("tbody").item(0);
	var elmTr_body   = elmTbody.getElementsByTagName("tr");
	var elmTr_filter = this._Create_Element({ element : "tr" });

	// table �̓��e�擾�ƃt�B���^�\���p�̗v�f����
	var arrayCols = new Array();
	var numLen_tr = elmTr_body.length;
	for( var numRow = 0 ; numRow < numLen_tr ; numRow ++ )
	{
		var alias_childNodes = elmTr_body[numRow].childNodes;
		for( var numCol = 0 ; numCol < alias_childNodes.length
			; numCol ++ )
		{
			var alias_col = alias_childNodes[numCol];

			if( alias_col.nodeType != 1 )
			{
				// �v�f�ȊO����菜��
				elmTr_body[numRow].removeChild(alias_col);
				numCol --;
				continue;
			}// if //

			if( numRow == 0 )
			{
				var elmTh_head = this._Create_Element({
									element : "th",
									attr    : { scope : "row" }
								});
				elmTr_filter.appendChild(elmTh_head);

				arrayCols[numCol] = new Array();
			}// if //

			arrayCols[numCol][numRow] = this._Get_TextContent(alias_col);
		}// for //
	}// for //
	this.elmTbody = elmTbody.cloneNode(true);

	// �t�B���^�� 1 ���ǉ����Ă����ƃJ�N�J�N�Ƃ����\���ƂȂ邽��
	// �gdisplay : none�h�ɂ��Ēǉ����s��
	var elmThead  = elmTable.getElementsByTagName("thead").item(0);
	elmTr_filter.style.display = "none";
	elmThead.appendChild(elmTr_filter);
	this._Rewrite_Filter(elmTr_filter, arrayCols);
	try
	{
		elmTr_filter.style.display = "table-row";
	}catch(e){
		elmTr_filter.style.display = "block";
	}// try //

	return 0;
};// classAutofilter.prototype.Create_Filter //

//
// �t�B���^��K�p
//
//	@param elmSelect select �v�f
//
classAutofilter.prototype.Select_Filter = function(elmSelect)
{
	var arrayFilters  = new Array();	// ���ݑI������Ă���t�B���^�̒l
	var boolAll       = true;			// �S�t�B���^�� VAL_FILTER_ALL ��
	var elmTr_filter  = elmSelect.parentNode.parentNode;
	var elm_select    = elmTr_filter.getElementsByTagName("select");
	var numLen_select = elm_select.length;
	for( var numCol = 0 ; numCol < numLen_select ; numCol ++ )
	{
		arrayFilters[numCol] = this._Get_SelectValue(elm_select[numCol]);

		if( arrayFilters[numCol] != this.VAL_FILTER_ALL )
		{
			// �S�t�B���^�� VAL_FILTER_ALL �ɂ����Ƃ�
			// �����ł������\�������ɖ߂����߁A�S�t�B���^���`�F�b�N

			boolAll = false;
		}// if //
	}// for //

	var elmTbody_new = this.elmTbody.cloneNode(true);
	var elmTr_body   = elmTbody_new.getElementsByTagName("tr");
	var numRow = 0;
	if( !boolAll )
	{
		// �����ꂩ�̃t�B���^�� VAL_FILTER_ALL �ȊO��I��

		for( numRow = 0 ; numRow < elmTr_body.length ; numRow ++ )
		{
			var alias_row         = elmTr_body[numRow];
			var numLen_childNodes = alias_row.childNodes.length;
			for( numCol = 0 ; numCol < numLen_childNodes ; numCol ++ )
			{
				if( arrayFilters[numCol] == this.VAL_FILTER_ALL )
				{
					continue;
				}// if //

				var strCell = this._Get_TextContent(alias_row.childNodes[numCol]);
				if( strCell == null )	strCell = "";	// ��

				if( arrayFilters[numCol] != this.VAL_FILTER_ALL
					&& arrayFilters[numCol] != strCell )
				{
					// �t�B���^�K�p

					elmTbody_new.removeChild(alias_row);
					numRow --;
					break;
				}// if //
			}// for //
		}// for //
	}// if //

	var arrayCols = new Array();	// �t�B���^�p�̗�f�[�^
	var numLen_tr = elmTr_body.length;
	for( numRow = 0 ; numRow < numLen_tr ; numRow ++ )
	{
		var alias_row         = elmTr_body[numRow];
		var numLen_childNodes = alias_row.childNodes.length;
		for( numCol = 0 ; numCol < numLen_childNodes ; numCol ++ )
		{
			if( arrayCols[numCol] == null )
			{
				arrayCols[numCol] = new Array();
			}// if //
			arrayCols[numCol][numRow]
				= this._Get_TextContent(alias_row.childNodes[numCol]);
		}// for //
	}// for //

	this._Rewrite_Filter(elmTr_filter, arrayCols);

	var elmThead = elmTr_filter.parentNode;
	var elmTable = elmThead.parentNode;
	var elmTbody = elmTable.getElementsByTagName("tbody").item(0);
	elmTable.removeChild(elmTbody);
	elmTable.appendChild(elmTbody_new);
};// classAutofilter.prototype.Select_Filter //

//
// �t�B���^�������̔�r
//
//	@param  val_a ��r�Ώ� A
//	@param  val_b ��r�Ώ� B
//	@return       ��r����
//
classAutofilter.prototype.Compare_Filter = function(val_a, val_b)
{
	if( !isNaN(val_a) && !isNaN(val_b) )
	{
		val_a = Number(val_a);
		val_b = Number(val_b);
	}// if //

	return ( val_a < val_b ) ? 1 : ( val_a > val_b ) ? -1 : 0;
};// classAutofilter.prototype.Compare_Filter //


// ----- private �Ȋ֐��Ƃ��Ĉ��� -----


//
// �t�B���^�� select �v�f�̐���/��������
//
//	@param arrayCols ��f�[�^
//
classAutofilter.prototype._Rewrite_Filter = function(elmTr_filter, arrayCols)
{
	var elm_select = elmTr_filter.getElementsByTagName("select");

	if( elm_select.length == 0 )	elm_select = null;

	var numLen_arrayCols = arrayCols.length;
	for( var numCol = 0 ; numCol < numLen_arrayCols ; numCol ++ )
	{
		var alias_cols = arrayCols[numCol];

		alias_cols.sort(this.Compare_Filter);

		var elmSelect_new = ( elm_select != null )
							? elm_select[numCol].cloneNode(false)
							: this._Create_Element({ element : "select" });
		var class_pointer = this;
		elmSelect_new.onchange = function(){ class_pointer.Select_Filter(this); };

		var strSelect = null;
		var elmOption = this._Create_Element({
							element : "option",
							attr    : { value : this.VAL_FILTER_ALL },
							content : this.LABEL_FILTER_ALL
						});
		if( elm_select == null )
		{
			elmOption.defaultSelected = true;
			elmOption.selected        = true;
		}else{
			strSelect = this._Get_SelectValue(elm_select[numCol]);
			if( strSelect == this.VAL_FILTER_ALL )
			{
				elmOption.defaultSelected = true;
				elmOption.selected        = true;
			}// if //
		}// if //
		elmSelect_new.appendChild(elmOption);

		var numLen_cols = alias_cols.length;
		for( var i = 0 ; i < numLen_cols ; i ++ )
		{
			if( i > 0 && alias_cols[i] != alias_cols[i - 1] || i == 0 )
			{
				var alias_col = alias_cols[i];

				var strValue   = null;
				var strContent = null;
				if( alias_col != null && alias_col.length > 0 )
				{
					// �Z�����󗓈ȊO

					strValue   = alias_col;
					strContent = strValue;
				}else{
					// �Z������

					strValue   = "";
					strContent = this.LABEL_FILTER_BLANK;
					alias_col  = strValue;
				}// if //
				elmOption = this._Create_Element({
								element : "option",
								attr    : { value : strValue },
								content : strContent
							});
				if( strSelect == alias_col )
				{
					elmOption.defaultSelected = true;
					elmOption.selected        = true;
				}// if //
				elmSelect_new.appendChild(elmOption);
			}// if //
		}// for //

		if( elm_select != null )
		{
			elmTr_filter.childNodes[numCol].removeChild(elm_select[numCol]);
		}// if //
		elmTr_filter.childNodes[numCol].appendChild(elmSelect_new);
	}// for //
};// classAutofilter.prototype._Rewrite_Filter //

//
// �v�f����
//
//	@param  argv �v�f�̏��
//	@return      �������ꂽ�v�f
//
classAutofilter.prototype._Create_Element = function(argv)
{
	var elm = document.createElement(argv.element);

	if( argv.attr )
	{
		var alias_attr = argv.attr;

		for( var i in alias_attr )
		{
			// �u���E�U�ɂ���Ă͓���ɖ��̂��� setAttribute ��p���Ȃ�

			elm[i] = alias_attr[i];
		}// for //
	}// if //

	if( argv.content )
	{
		var nodeText = document.createTextNode(argv.content);
		elm.appendChild(nodeText);
	}// if //

	return elm;
};// classAutofilter.prototype._Create_Element //

//
// select �v�f�őI������Ă��� value �����l���擾
//
//	@param  elmSelect select �v�f
//	@return           �I������Ă��� value �����l
//
classAutofilter.prototype._Get_SelectValue = function(elmSelect)
{
	return elmSelect.options[elmSelect.selectedIndex].value;
};// classAutofilter.prototype._Get_SelectValue //

//
// �v�f����e�L�X�g�̂ݎ擾
//
//	@param  elm �v�f
//	@return     �e�L�X�g
//
classAutofilter.prototype._Get_TextContent = function(elm)
{
	return ( typeof(elm.textContent) != "undefined" )
			? elm.textContent : elm.innerText;
};// classAutofilter.prototype._Get_TextContent //


// ===== �{�^���̏��� =========================================


//
// �I�[�g�t�B���^��\������{�^��
//
//	@param elmInput �{�^���̗v�f
//	@param strId    �I�[�g�t�B���^��\������ table �� ID
//
function Button_DispFilter(elmInput, strId)
{
	// �gdisplay : none�h��p���ăI�[�g�t�B���^�̕\��/��\����؂�ւ����
	// Opera 8.54 �ł͈�x��\���ɂ������� select �v�f�őI������Ă��镨��
	// �ύX���悤�Ƃ��Ă��ύX�ł��Ȃ��Ȃ�
	// ���̂��߁g�\���{�^���h�̎g�p�͈�x����Ƃ��A�{�^���͎��ł�����

	var strMes = "���g���̃u���E�U�ł̓I�[�g�t�B���^��\���ł��܂���";

	this.boolExec = false;
	var objInterval = setInterval(
						function()
						{
							clearInterval(objInterval);
							(function()
							{
								if( this.boolExec )	return;

								this.boolExec = true;

								var cAutofilter = new classAutofilter();
								if( cAutofilter.Create_Filter(strId) < 0 )
								{
									alert(strMes);
									return;
								}// if //

								// �{�^���̎���
								var elm_parent = elmInput.parentNode;
								elm_parent.removeChild(elmInput);
							})();
						},// function //
						200
					);
}// Button_DispFilter //

/*
	table �̃\�[�g 2
	http://neko.dosanko.us/script/sort_table2/
	2006-12-4 ��

	�Ƃقق�WWW����́u�e�[�u�����\�[�g����(2003/2/2��)�v���x�[�X
	http://www.tohoho-web.com/wwwxx038.htm
*/

function classSortTable()
{
	// �ݒ� �������火

	this.MES_ALERT = "���g���̃u���E�U�ł̓\�[�g�@�\�𗘗p�ł��܂���";

	this.ORDER_DEFAULT = -1;

	// �ݒ� �����܂Ł�

	this.numOrder      = this.ORDER_DEFAULT;	// ���݂̃\�[�g����
	this.arrayColumn   = null;					// ���݃\�[�g���s���Ă����̗D�揇��
	this.arrayCol_Last = new Array();			// �Ō�Ƀ\�[�g������
}// classSortTable //

var g_cSortTable = new classSortTable();


// ----- public �Ȋ֐��Ƃ��Ĉ��� -----


//
// �\�[�g�{�^��
//
//	@param strId_table �Ώ� table �� id
//	@param arrayColumn �\�[�g�����Ƃ����(���A���c�ƃ\�[�g����D�揇�ɔz��Ŏw��)
//
classSortTable.prototype.Button_Sort = function(strId_table, arrayColumn)
{
	var class_pointer = this;

	class_pointer.boolExec = false;
	var objInterval = setInterval(
						function()
						{
							clearInterval(objInterval);
							(function()
							{
								if( class_pointer.boolExec )	return;
								class_pointer.boolExec = true;

								class_pointer._Sort_Table(strId_table, arrayColumn);
							})();
						},// function //
						200
					);
};// classSortTable.prototype.Button_Sort //

//
// �s�̔�r
//
//	@param  elmTr_a ��r�Ώۂ̍s A
//	@param  elmTr_b ��r�Ώۂ̍s B
//	@return         ��r����
//
classSortTable.prototype.Compare = function(elmTr_a, elmTr_b)
{
	var arrayColumn = this.arrayColumn;	// �D�揇��
	var numOrder    = this.numOrder;	// �\�[�g����
	var numResult   = 0;				// ��r����

	var val_a = null;	// �s A �̃Z���̒l
	var val_b = null;	// �s B �̃Z���̒l

	var numLen_arrayColumn = arrayColumn.length;
	for( var i = 0 ; i < numLen_arrayColumn && numResult == 0 ; i ++ )
	{
		var alias_arrayColumn = arrayColumn[i];

		if( typeof(elmTr_a.textContent) != "undefined" )
		{
			val_a = elmTr_a.childNodes[alias_arrayColumn].textContent;
			val_b = elmTr_b.childNodes[alias_arrayColumn].textContent;
		}else{
			val_a = elmTr_a.childNodes[alias_arrayColumn].innerText;
			val_b = elmTr_b.childNodes[alias_arrayColumn].innerText;
		}// if //

		if( (!isNaN(val_a) || val_a.length < 1)
			&& (!isNaN(val_b) || val_b.length < 1) )
		{
			// �Z���������Ƃ��g�������󔒁h�Ȃ琔�l�Ƃ��ă\�[�g
			// Number.NEGATIVE_INFINITY ���󔒂̑�ւƂ���

			val_a = ( val_a.length > 0 ) ? Number(val_a) : Number.NEGATIVE_INFINITY;
			val_b = ( val_b.length > 0 ) ? Number(val_b) : Number.NEGATIVE_INFINITY;
		}// if //

		if( val_a < val_b  )
		{
			numResult = ( numOrder == -1 ) ? 1 : -1;
		}else if( val_a > val_b ){
			numResult = ( numOrder != -1 ) ? 1 : -1;
		}// if //
	}// for //

	return numResult;
};// classSortTable.prototype.Compare //


// ----- private �Ȋ֐��Ƃ��Ĉ��� -----


//
//	�\�[�g
//
//	@param strId_table �Ώ� table �� id
//	@param arrayColumn �\�[�g�����Ƃ����(���A���c�ƃ\�[�g����D�揇�ɔz��Ŏw��)
//
classSortTable.prototype._Sort_Table = function(strId_table, arrayColumn)
{
	// �ΏۊO�u���E�U�̃`�F�b�N
	if( !document.getElementById || !document.removeChild )
	{
		alert(this.MES_ALERT);
		return;
	}// if //

	var elmTable = document.getElementById(strId_table);
	var elmTbody = elmTable.getElementsByTagName("tbody").item(0);
	var elmTr    = elmTbody.getElementsByTagName("tr");
	var arrayTr  = new Array();

	// ���݂̓��e���擾
	var numLen_tr = elmTr.length;
	for( var i = 0 ; i < numLen_tr ; i ++ )
	{
		var alias_tr = elmTr[i];
		var alias_childNodes = alias_tr.childNodes;
		for( var j = 0 ; j < alias_childNodes.length ; j ++ )
		{
			if( alias_childNodes[j].nodeType != 1 )
			{
				// �v�f�ȊO����菜��
				alias_tr.removeChild(alias_childNodes[j]);
				j --;
			}// if //
		}// for //

		arrayTr[i] = alias_tr.cloneNode(true);
	}// for //

	var alias_arrayCol_Last = this.arrayCol_Last;
	if( alias_arrayCol_Last[strId_table] == null )	alias_arrayCol_Last[strId_table] = -1;

	// ������̃\�[�g�́A�\�[�g�����𔽓]
	this.numOrder = ( alias_arrayCol_Last[strId_table] == arrayColumn[0] )
					? this.numOrder * -1 : this.ORDER_DEFAULT;

	this.arrayColumn = arrayColumn;
	alias_arrayCol_Last[strId_table] = arrayColumn[0];

	var class_pointer = this;
	arrayTr.sort(function(elm_a, elm_b){ return class_pointer.Compare(elm_a, elm_b); });

	// �\�[�g����
	var elmTbody_result = elmTbody.cloneNode(false);
	var numLen_tr = arrayTr.length;
	for( i = 0 ; i < numLen_tr ; i ++ )
	{
		elmTbody_result.appendChild(arrayTr[i]);
	}// for //
	elmTable.removeChild(elmTbody)
	elmTable.appendChild(elmTbody_result);
};// classSortTable.prototype._Sort_Table //

