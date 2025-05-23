<?php

namespace App\Http\Controllers;

use App\Helpers\Fungsi;
use App\Imports\importnilaipermateri;
use App\Models\dataajar;
use App\Models\guru;
use App\Models\kelas;
use App\Models\kompetensidasar;
use App\Models\mapel;
use App\Models\materipokok;
use App\Models\siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class gurupenilaiancontroller extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if(Auth::user()->tipeuser!='guru'){
                return redirect()->route('dashboard')->with('status','Halaman tidak ditemukan!')->with('tipe','danger');
            }

        return $next($request);

        });
    }
    public function index(Request $request)
    {
        $guru_id=guru::where('nomerinduk',Auth::user()->nomerinduk)->pluck('id');
        #WAJIB
        $pages='penilaian';
        $datas=dataajar::with('guru')->with('kelas')->with('mapel')->where('guru_id',$guru_id)
        ->paginate(Fungsi::paginationjml());
        $guru=guru::get();
        $kelas=kelas::get();

        return view('pages.guru.penilaian.index',compact('datas','request','pages','guru','kelas'));
    }
    public function cari(Request $request)
    {

        $guru_id=guru::where('nomerinduk',Auth::user()->nomerinduk)->pluck('id');
        $cari=$request->cari;
        #WAJIB
        $pages='penilaian';
        $datas=dataajar::with('guru')->with('kelas')->with('mapel')->where('guru_id',$guru_id)
        ->where('nama','like',"%".$cari."%")
        ->where('kelas_id','like',"%".$request->kelas_id."%")
        ->paginate(Fungsi::paginationjml());
        $guru=guru::get();
        $kelas=kelas::get();

        return view('pages.guru.penilaian.index',compact('datas','request','pages','guru','kelas'));
    }

    public function inputnilai(dataajar $dataajar,Request $request)
    {
        #WAJIB
        $pages='penilaian';
        $datasiswa=siswa::where('kelas_id',$dataajar->kelas_id)->paginate(Fungsi::paginationjml());
        $datakd=kompetensidasar::with('materipokok')
        ->where('dataajar_id',$dataajar->id)
        ->orderBy('kode','asc')
        ->get();
        // $datasnilai=new Collection();
        //     foreach($datasiswa as $siswa){
        //         $datasnilai->push((object)[
        //             'id' => $request->id1,
        //             'nomerinduk'=>$request->nomerinduk,
        //             'nama'=>$request->nama,
        //             'kelas_id'=>$request->kelas_id,
        //             'materipokok_id'=>'1',
        //             'nilai'=>'90'
        //         ]);
        //     }
        // $mapel = $dataajar->mapel;
        $datas=$datasiswa;

        // $datas = $datasiswa->map(function ($item, $key) {
        //     return $item * 2;
        // });
        // dd($datas);
        $mapel=mapel::where('id',$dataajar->mapel_id)->first();

        return view('pages.admin.penilaian.inputnilai',compact('datas','request','pages','dataajar','datakd','mapel'));
    }

    public function importnilaipermateri(dataajar $dataajar,materipokok $materipokok,Request $request){

		// dd($request,$materipokok);
		$this->validate($request, [
			'file' => 'required|mimes:csv,xls,xlsx'
		]);

		$file = $request->file('file');

		$nama_file = rand().$file->getClientOriginalName();

		$file->move('file_temp',$nama_file);

		Excel::import(new importnilaipermateri($dataajar,$materipokok), public_path('/file_temp/'.$nama_file));

        return redirect()->back()->with('status','Data berhasil Diimport!')->with('tipe','success')->with('icon','fas fa-edit');

	}
}
