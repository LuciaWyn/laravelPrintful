<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


use Printful\Exceptions\PrintfulApiException;
use Printful\Exceptions\PrintfulException;
use Printful\PrintfulApiClient;
use Printful\PrintfulProducts;
use Printful\Structures\Sync\Responses\SyncProductsResponse;
use Printful\PrintfulTaxRates;

use App\Models\printful_country;
use App\Models\printful_states;
use App\Models\printful_address;
use App\Models\printful_store_info;
use App\Models\printful_product;
use App\Models\printful_variant;
use App\Models\printful_file;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class printful extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = file_get_contents('https://api.printful.com/countries');
        $countries= json_decode($countries); //['result'];
        $countries = $countries->result;
        foreach($countries as $country){
            $newcountry = new printful_country;
            $newcountry->name = $country->name;
            $newcountry->code = $country->code;
            $newcountry->save();
            $this->command->info('Created country '.$country->name);
            if($country->states !== null){
                $countryid = printful_country::where(['name'=>$country->name, 'code'=>$country->code])->pluck('id')->first();
                $states = $country->states;
                foreach($states as $state){
                    $newstate = new printful_states;
                    $newstate->name = $state->name;
                    $newstate->code = $state->code;
                    $newstate->country_id = $countryid;
                    $newstate->save();
                    $this->command->info('Created state '.$state->name);
                }
            }
        }
        $taxable = file_get_contents('https://api.printful.com/tax/countries');
        $taxable = json_decode($taxable);
        foreach($taxable->result as $country){
            $countryid = printful_country::where(['name'=>$country->name, 'code'=>$country->code])->pluck('id')->first();
            $states = $country->states;
            if($states !== null){
                foreach($states as $state){
                    $stateEdit = printful_states::where(['name'=>$state->name, 'code'=>$state->code])->first();
                    if($state->shipping_taxable !== false){
                        $stateEdit->shipping_taxable = true;
                        $stateEdit->save();
                        $this->command->info('State edited '.$state->name);
                    }
                }
            }
        }

        $response = Http::withBasicAuth(
            env('PRINTFUL_API_KEY'), ''
        )->get('https://api.printful.com/store');
        $response = $response->json();
        $response = $response['result'];
        $returnAddressid = null;
        if($response["return_address"] != null){
            $returnAddressArray = $response["return_address"];

            $countryid = printful_country::where([
                'code' => $returnAddressArray['country_code'],
                'name' => $returnAddressArray['country_name']
            ])->pluck('id')->first();

            $stateid = null;
            if($returnAddressArray['state_code']!= null){
                $stateid = printful_states::where([
                    'code' => $returnAddressArray['state_code'],
                    'name' => $returnAddressArray['state_name'],
                    'country_id' => $countryid
                ])->pluck('id')->first();
            }

            $returnAddress = new printful_address;
            $returnAddress->name = $returnAddressArray['name'];
            $returnAddress->company_name = $returnAddressArray['company'];
            $returnAddress->address1 = $returnAddressArray['address1'];
            $returnAddress->address2 = $returnAddressArray['address2'];
            $returnAddress->city = $returnAddressArray['city'];
            $returnAddress->country_id = $countryid;
            $returnAddress->state_id = $stateid;
            $returnAddress->zip = $returnAddressArray['zip'];
            $returnAddress->phone = $returnAddressArray['phone'];
            $returnAddress->email = $returnAddressArray['email'];
            $returnAddress->type = 'return';
            $returnAddress->save();
            $returnAddressid = printful_address::where([
                'name' => $returnAddressArray['name'],
                'company_name' => $returnAddressArray['company'],
                'address1' => $returnAddressArray['address1'],
                'address2' => $returnAddressArray['address2'],
                'city' => $returnAddressArray['city'],
                'country_id' => $countryid,
                'state_id' => $stateid,
                'zip' => $returnAddressArray['zip'],
                'phone' => $returnAddressArray['phone'],
                'email' => $returnAddressArray['email'],
                'type' => 'return'
            ])->pluck('id')->first();

            $this->command->info('return address saved');
        }

        $billingAddressArray = $response["billing_address"];

        $countryid = printful_country::where([
            'code' => $billingAddressArray['country_code'],
            'name' => $billingAddressArray['country_name']
        ])->pluck('id')->first();

        $stateid = null;
        if($billingAddressArray['state_code']!= null){
            $stateid = printful_states::where([
                'code' => $billingAddressArray['state_code'],
                'name' => $billingAddressArray['state_name'],
                'country_id' => $countryid
            ])->pluck('id')->first();
        }

        $billingAddress = new printful_address;
        $billingAddress->name = $billingAddressArray['name'];
        $billingAddress->company_name = $billingAddressArray['company'];
        $billingAddress->address1 = $billingAddressArray['address1'];
        $billingAddress->address2 = $billingAddressArray['address2'];
        $billingAddress->city = $billingAddressArray['city'];
        $billingAddress->country_id = $countryid;
        $billingAddress->state_id = $stateid;
        $billingAddress->zip = $billingAddressArray['zip'];
        $billingAddress->phone = $billingAddressArray['phone'];
        $billingAddress->email = $billingAddressArray['email'];
        $billingAddress->type = 'billing';
        $billingAddress->save();
        $billingAddressid = printful_address::where([
            'name' => $billingAddressArray['name'],
            'company_name' => $billingAddressArray['company'],
            'address1' => $billingAddressArray['address1'],
            'address2' => $billingAddressArray['address2'],
            'city' => $billingAddressArray['city'],
            'country_id' => $countryid,
            'state_id' => $stateid,
            'zip' => $billingAddressArray['zip'],
            'phone' => $billingAddressArray['phone'],
            'email' => $billingAddressArray['email'],
            'type' => 'billing'
        ])->pluck('id')->first();

        $this->command->info('Billing Address found.');
        
        $store = new printful_store_info;
        $store->store_id = $response['id'];
        $store->name = $response['name'];
        $store->website = $response['website'];
        $store->return_address_id = $returnAddressid;
        $store->billing_address_id = $billingAddressid;
        $store->currency = $response['currency'];
        $store->type = $response['type'];
        $store->packaging_email = $response['packing_slip']['email'];
        $store->packaging_phone = $response['packing_slip']['phone'];
        $store->packaging_message = $response['packing_slip']['message'];
        $store->packaging_logo_url = null;
        $store->created = $response['created'];
        $store->save();

        $this->command->info('Store information saved.');

        
        $apikey = env('PRINTFUL_API_KEY');
        $pf = new PrintfulApiClient($apikey);
        $productsApi = new PrintfulProducts($pf);
        $this->products($productsApi);
        
        //dd('finished');
        $products = printful_product::get();
        foreach($products as $product){
            $response = $productsApi->getProduct($product->printful_id);
            $productId = $product->id;
            $variants = $response->syncVariants;
            $first = true;
            foreach($variants as $variant){
                $newvariant = new printful_variant;
                $newvariant->product_id = $productId;
                $newvariant->printful_id = $variant->id;
                $newvariant->external_id = $variant->externalId;
                $newvariant->name = $variant->name;
                $newvariant->sync_product_id = $variant->syncProductId;
                $newvariant->printful_variant_id = $variant->variantId;
                $newvariant->retail_price = $variant->retailPrice;
                $newvariant->currency = $variant->currency;
                $newvariant->save();
                $this->command->info('New variant '.$variant->name.' saved.');

                $newvariantId = printful_variant::where([
                    'product_id' => $productId,
                    'printful_id' => $variant->id,
                    'external_id' => $variant->externalId
                ])->pluck('id')->first();
                
                $files = $variant->files;
                $variantfirst = true;
                foreach($files as $file){
                    $thumbnail = $file->thumbnailUrl;
                    $thumbnailcontent = file_get_contents($thumbnail);
                    $thumbnailname = 'public/printfulProducts'.substr($thumbnail, strrpos($thumbnail, '/'));
                    $thumbnail = Storage::put($thumbnailname, $thumbnailcontent);

                    $preview = $file->previewUrl;
                    $previewcontent = file_get_contents($preview);
                    $previewname = 'public/printfulProducts'.substr($preview, strrpos($preview, '/'));
                    $preview = Storage::put($previewname, $previewcontent);

                    $newfile = new printful_file;
                    $newfile->type = $file->type;
                    $newfile->hash = $file->hash;
                    $newfile->variant_id = $newvariantId;
                    $newfile->url = $file->url;
                    $newfile->filename = $file->filename;
                    $newfile->mime_type = $file->mimeType;
                    $newfile->size = $file->size;
                    $newfile->width = $file->width;
                    $newfile->height = $file->height;
                    $newfile->dpi = $file->dpi;
                    $newfile->status = $file->status;
                    $newfile->created = $file->created;
                    $newfile->thumbnail_url = $file->thumbnailUrl;
                    $newfile->preview_url = $file->previewUrl;
                    $newfile->visible = $file->visible;
                    $newfile->save();

                    if($file->type == "preview"){
                        if($variantfirst ==true){
                            $newvariant->thumbnail_url = $thumbnail;
                            $newvariant->preview_url = $preview;
                            $newvariant->save();
                            $variantfirst = false;
                        }
                        if(($first == true)){
                            $product->thumbnail_url = $thumbnail;
                            $product->save();
                            $first = false;
                        }
                    }
                    
                }
                

                
            }
        }
        
        
    }

    private function products($api){

        $productlist = $api->getProducts(0, 5);
        foreach($productlist->result as $product){
            $this->addproduct($product);
        }
        if($productlist->paging->limit < $productlist->paging->total){
            for($limit = 6; $limit<= $productlist->paging->total; $limit = ($limit+5)){
                $productlist =  $api->getProducts($limit, ($limit+4));
                foreach($productlist->result as $product){
                    $this->addproduct($product);
                }
            }
        }
    }

    private function addproduct($product){
        $productnew = new printful_product;
        $productnew->printful_id = $product->id;
        $productnew->external_id = $product->externalId;
        $productnew->name = $product->name;
        $productnew->variants = $product->variants;
        $productnew->synced = $product->synced;
        $productnew->save();
        $this->command->info('Product '.$product->name." added");
    }
}
